#!/bin/zsh
# Optimized script to run both Laravel and Go servers
# This script handles dependencies, health checks, and provides better user experience

set -e  # Exit on any error

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)"
GO_SERVER_DIR="$SCRIPT_DIR/goserver"
LARAVEL_DIR="$SCRIPT_DIR/Pendataan_IGD"
GO_PORT=8080
LARAVEL_PORT=8000

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Helper functions
print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Check if port is available
check_port() {
    local port=$1
    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1; then
        return 1  # Port is in use
    else
        return 0  # Port is available
    fi
}

# Wait for server to be ready
wait_for_server() {
    local url=$1
    local name=$2
    local max_attempts=30
    local attempt=1

    print_info "Waiting for $name to start..."
    
    while [ $attempt -le $max_attempts ]; do
        if curl -s -f "$url" >/dev/null 2>&1; then
            print_success "$name is ready at $url"
            return 0
        fi
        
        echo -n "."
        sleep 1
        ((attempt++))
    done
    
    print_error "$name failed to start within $max_attempts seconds"
    return 1
}

# Check dependencies
check_dependencies() {
    print_info "Checking dependencies..."
    
    # Check Go
    if ! command -v go &> /dev/null; then
        print_error "Go is not installed. Please install Go first."
        exit 1
    fi
    
    # Check PHP
    if ! command -v php &> /dev/null; then
        print_error "PHP is not installed. Please install PHP first."
        exit 1
    fi
    
    # Check if Go server binary exists, if not build it
    if [[ ! -f "$GO_SERVER_DIR/goserver" ]]; then
        print_info "Go server binary not found. Building..."
        cd "$GO_SERVER_DIR"
        if make build; then
            print_success "Go server built successfully"
        else
            print_error "Failed to build Go server"
            exit 1
        fi
        cd "$SCRIPT_DIR"
    fi
    
    # Check Laravel dependencies
    if [[ ! -f "$LARAVEL_DIR/vendor/autoload.php" ]]; then
        print_warning "Laravel dependencies not installed. Run 'composer install' in $LARAVEL_DIR"
    fi
    
    print_success "All dependencies checked"
}

# Kill existing servers on the same ports
cleanup_ports() {
    print_info "Cleaning up existing processes..."
    
    # Kill processes on Go port
    if ! check_port $GO_PORT; then
        print_warning "Port $GO_PORT is in use. Attempting to free it..."
        lsof -ti:$GO_PORT | xargs kill -9 2>/dev/null || true
        sleep 2
    fi
    
    # Kill processes on Laravel port
    if ! check_port $LARAVEL_PORT; then
        print_warning "Port $LARAVEL_PORT is in use. Attempting to free it..."
        lsof -ti:$LARAVEL_PORT | xargs kill -9 2>/dev/null || true
        sleep 2
    fi
    
    print_success "Ports cleaned up"
}

# Start Go server
start_go_server() {
    print_info "Starting Go API server..."
    
    cd "$GO_SERVER_DIR"
    
    # Start in background and capture PID
    nohup ./goserver > log.txt 2>&1 &
    GO_PID=$!
    echo $GO_PID > go_server.pid
    
    cd "$SCRIPT_DIR"
    
    # Wait for server to be ready
    if wait_for_server "http://localhost:$GO_PORT/api/health" "Go API Server"; then
        return 0
    else
        return 1
    fi
}

# Start Laravel server
start_laravel_server() {
    print_info "Starting Laravel web server..."
    
    cd "$LARAVEL_DIR"
    
    # Start in background and capture PID
    nohup php artisan serve --port=$LARAVEL_PORT > storage/logs/serve.log 2>&1 &
    LARAVEL_PID=$!
    echo $LARAVEL_PID > laravel_server.pid
    
    cd "$SCRIPT_DIR"
    
    # Wait for server to be ready
    if wait_for_server "http://localhost:$LARAVEL_PORT" "Laravel Server"; then
        return 0
    else
        return 1
    fi
}

# Show running servers status
show_status() {
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    print_success "🚀 Both servers are running successfully!"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    print_info "📊 Server Information:"
    echo "   🐹 Go API Server:     http://localhost:$GO_PORT"
    echo "   🐘 Laravel Server:    http://localhost:$LARAVEL_PORT"
    echo ""
    print_info "🔗 Available Endpoints:"
    echo "   📋 API Documentation: http://localhost:$LARAVEL_PORT/api"
    echo "   🏥 Health Check:      http://localhost:$GO_PORT/api/health"
    echo "   📊 Dashboard:         http://localhost:$LARAVEL_URL"
    echo ""
    print_info "🛠️  Management Commands:"
    echo "   Stop servers:         ./stop_servers.sh"
    echo "   Test migration:       ./test_migration.sh"
    echo "   Server logs:          tail -f goserver/log.txt"
    echo "   Laravel logs:         tail -f Pendataan_IGD/storage/logs/serve.log"
    echo ""
    
    # Save PIDs for easy stopping
    cat > server_pids.txt << EOF
GO_PID=$GO_PID
LARAVEL_PID=$LARAVEL_PID
GO_PORT=$GO_PORT
LARAVEL_PORT=$LARAVEL_PORT
EOF
    
    print_success "Server PIDs saved to server_pids.txt"
}

# Cleanup function for proper shutdown
cleanup() {
    echo ""
    print_info "Shutting down servers..."
    
    if [[ -n "$GO_PID" ]] && kill -0 $GO_PID 2>/dev/null; then
        kill $GO_PID
        print_info "Go server stopped"
    fi
    
    if [[ -n "$LARAVEL_PID" ]] && kill -0 $LARAVEL_PID 2>/dev/null; then
        kill $LARAVEL_PID
        print_info "Laravel server stopped"
    fi
    
    exit 0
}

# Set up signal handlers
trap cleanup SIGINT SIGTERM

# Main execution
main() {
    echo "🏥 Pendataan IGD - Laravel to Go Migration"
    echo "=========================================="
    echo ""
    
    check_dependencies
    cleanup_ports
    
    # Start servers
    if start_go_server; then
        if start_laravel_server; then
            show_status
            
            # Keep script running and wait for user interrupt
            print_info "Press Ctrl+C to stop both servers"
            while true; do
                sleep 1
            done
        else
            print_error "Failed to start Laravel server"
            # Clean up Go server if Laravel fails
            [[ -n "$GO_PID" ]] && kill $GO_PID 2>/dev/null
            exit 1
        fi
    else
        print_error "Failed to start Go server"
        exit 1
    fi
}

# Check for command line arguments
case "${1:-start}" in
    "start")
        main
        ;;
    "status")
        if [[ -f "server_pids.txt" ]]; then
            source server_pids.txt
            echo "Server Status:"
            echo "Go Server (PID: $GO_PID) - Port: $GO_PORT"
            echo "Laravel Server (PID: $LARAVEL_PID) - Port: $LARAVEL_PORT"
        else
            print_warning "No server PIDs found. Servers may not be running."
        fi
        ;;
    "help"|"-h"|"--help")
        echo "Usage: $0 [start|status|help]"
        echo ""
        echo "Commands:"
        echo "  start   - Start both servers (default)"
        echo "  status  - Show server status"
        echo "  help    - Show this help"
        ;;
    *)
        print_error "Unknown command: $1"
        echo "Use '$0 help' for usage information"
        exit 1
        ;;
esac
