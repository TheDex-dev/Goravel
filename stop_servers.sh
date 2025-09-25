#!/bin/zsh
# Script to stop both Laravel and Go servers gracefully

set -e

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)"
GO_SERVER_DIR="$SCRIPT_DIR/goserver"
LARAVEL_DIR="$SCRIPT_DIR/laravel"

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

# Stop servers using PIDs
stop_servers_by_pid() {
    local stopped=0
    
    if [[ -f "$SCRIPT_DIR/bin/server_pids.txt" ]]; then
        source "$SCRIPT_DIR/bin/server_pids.txt"
        
        # Stop Go server
        if [[ -n "$GO_PID" ]] && kill -0 $GO_PID 2>/dev/null; then
            print_info "Stopping Go server (PID: $GO_PID)..."
            kill $GO_PID
            sleep 2
            if ! kill -0 $GO_PID 2>/dev/null; then
                print_success "Go server stopped"
                ((stopped++))
            else
                print_warning "Force killing Go server..."
                kill -9 $GO_PID 2>/dev/null || true
                ((stopped++))
            fi
        fi
        
        # Stop Laravel server
        if [[ -n "$LARAVEL_PID" ]] && kill -0 $LARAVEL_PID 2>/dev/null; then
            print_info "Stopping Laravel server (PID: $LARAVEL_PID)..."
            kill $LARAVEL_PID
            sleep 2
            if ! kill -0 $LARAVEL_PID 2>/dev/null; then
                print_success "Laravel server stopped"
                ((stopped++))
            else
                print_warning "Force killing Laravel server..."
                kill -9 $LARAVEL_PID 2>/dev/null || true
                ((stopped++))
            fi
        fi
        
        # Clean up PID files
        rm -f "$SCRIPT_DIR/bin/server_pids.txt"
        rm -f "$GO_SERVER_DIR/go_server.pid" 2>/dev/null || true
        rm -f "$LARAVEL_DIR/laravel_server.pid" 2>/dev/null || true
        
    else
        print_warning "No server PIDs file found. Attempting to stop by port..."
        return 1
    fi
    
    return 0
}

# Stop servers by port (fallback method)
stop_servers_by_port() {
    local stopped=0
    
    # Default ports
    local GO_PORT=${GO_PORT:-8080}
    local LARAVEL_PORT=${LARAVEL_PORT:-8000}
    
    # Stop Go server
    local go_pids=$(lsof -ti:$GO_PORT 2>/dev/null || true)
    if [[ -n "$go_pids" ]]; then
        print_info "Stopping processes on port $GO_PORT..."
        echo $go_pids | xargs kill -TERM 2>/dev/null || true
        sleep 2
        
        # Force kill if still running
        go_pids=$(lsof -ti:$GO_PORT 2>/dev/null || true)
        if [[ -n "$go_pids" ]]; then
            echo $go_pids | xargs kill -9 2>/dev/null || true
        fi
        print_success "Port $GO_PORT cleared"
        ((stopped++))
    fi
    
    # Stop Laravel server
    local laravel_pids=$(lsof -ti:$LARAVEL_PORT 2>/dev/null || true)
    if [[ -n "$laravel_pids" ]]; then
        print_info "Stopping processes on port $LARAVEL_PORT..."
        echo $laravel_pids | xargs kill -TERM 2>/dev/null || true
        sleep 2
        
        # Force kill if still running
        laravel_pids=$(lsof -ti:$LARAVEL_PORT 2>/dev/null || true)
        if [[ -n "$laravel_pids" ]]; then
            echo $laravel_pids | xargs kill -9 2>/dev/null || true
        fi
        print_success "Port $LARAVEL_PORT cleared"
        ((stopped++))
    fi
    
    if [[ $stopped -eq 0 ]]; then
        print_info "No servers found running on default ports"
    fi
}

# Show server status
show_status() {
    print_info "Checking server status..."
    
    local go_running=false
    local laravel_running=false
    
    # Check Go server
    if curl -s -f "http://localhost:8080/api/health" >/dev/null 2>&1; then
        go_running=true
        print_info "Go server is running on port 8080"
    fi
    
    # Check Laravel server
    if curl -s -f "http://localhost:8000" >/dev/null 2>&1; then
        laravel_running=true
        print_info "Laravel server is running on port 8000"
    fi
    
    if [[ "$go_running" == false ]] && [[ "$laravel_running" == false ]]; then
        print_success "No servers are currently running"
        return 0
    else
        return 1
    fi
}

# Main execution
main() {
    echo "🛑 Stopping Pendataan IGD Servers"
    echo "================================="
    echo ""
    
    # Try stopping by PID first
    if ! stop_servers_by_pid; then
        # Fallback to stopping by port
        stop_servers_by_port
    fi
    
    # Verify servers are stopped
    sleep 1
    if show_status; then
        echo ""
        print_success "All servers stopped successfully!"
    else
        echo ""
        print_warning "Some servers may still be running. Check manually if needed."
    fi
    
    echo ""
    print_info "To start servers again, run: ./start_servers.sh"
}

# Handle command line arguments
case "${1:-stop}" in
    "stop")
        main
        ;;
    "status")
        show_status
        if [[ $? -eq 0 ]]; then
            echo "All servers are stopped."
        else
            echo "Some servers are still running."
        fi
        ;;
    "force")
        print_warning "Force stopping all servers..."
        stop_servers_by_port
        print_success "Force stop completed"
        ;;
    "help"|"-h"|"--help")
        echo "Usage: $0 [stop|status|force|help]"
        echo ""
        echo "Commands:"
        echo "  stop    - Stop servers gracefully (default)"
        echo "  status  - Check if servers are running"
        echo "  force   - Force stop servers by killing processes on ports"
        echo "  help    - Show this help"
        ;;
    *)
        print_error "Unknown command: $1"
        echo "Use '$0 help' for usage information"
        exit 1
        ;;
esac