#!/bin/zsh
# Health check script for both Laravel and Go servers

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

# Configuration
GO_URL="http://localhost:8080"
LARAVEL_URL="http://localhost:8000"

# Check server health
check_server_health() {
    local url=$1
    local name=$2
    local health_endpoint=$3
    
    # Check if server responds
    if curl -s -f "$url$health_endpoint" >/dev/null 2>&1; then
        print_success "$name is healthy"
        
        # Get response time
        response_time=$(curl -o /dev/null -s -w "%{time_total}" "$url$health_endpoint")
        echo "   Response time: ${response_time}s"
        
        # Additional info for Go server
        if [[ "$name" == "Go API Server" ]]; then
            health_info=$(curl -s "$url$health_endpoint" 2>/dev/null || echo "{}")
            echo "   Health info: $health_info"
        fi
        
        return 0
    else
        print_error "$name is not responding"
        return 1
    fi
}

# Show detailed status
show_detailed_status() {
    echo "🏥 Pendataan IGD - Server Health Check"
    echo "======================================"
    echo ""
    
    local go_healthy=false
    local laravel_healthy=false
    
    # Check Go server
    print_info "Checking Go API Server..."
    if check_server_health "$GO_URL" "Go API Server" "/api/health"; then
        go_healthy=true
    fi
    
    echo ""
    
    # Check Laravel server
    print_info "Checking Laravel Server..."
    if check_server_health "$LARAVEL_URL" "Laravel Server" "/"; then
        laravel_healthy=true
    fi
    
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    
    if [[ "$go_healthy" == true ]] && [[ "$laravel_healthy" == true ]]; then
        print_success "🎉 All servers are healthy!"
        echo ""
        echo "🌐 Available URLs:"
        echo "   Go API:     $GO_URL"
        echo "   Laravel:    $LARAVEL_URL"
        echo "   Health:     $GO_URL/api/health"
        echo "   Test API:   Run './test_migration.sh'"
        
        # Show PIDs if available
        if [[ -f "server_pids.txt" ]]; then
            echo ""
            print_info "Server PIDs:"
            source server_pids.txt
            echo "   Go Server: $GO_PID"
            echo "   Laravel Server: $LARAVEL_PID"
        fi
        
    elif [[ "$go_healthy" == true ]] || [[ "$laravel_healthy" == true ]]; then
        print_warning "⚠️  Some servers are not healthy"
        
        if [[ "$go_healthy" == false ]]; then
            echo "   • Go server needs attention"
            echo "   • Check: tail -f goserver/log.txt"
        fi
        
        if [[ "$laravel_healthy" == false ]]; then
            echo "   • Laravel server needs attention"
            echo "   • Check: tail -f Pendataan_IGD/storage/logs/laravel.log"
        fi
        
    else
        print_error "💀 All servers are down!"
        echo ""
        print_info "To start servers:"
        echo "   ./start_servers.sh"
    fi
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
}

# Quick status check (for monitoring)
quick_check() {
    local go_status="❌"
    local laravel_status="❌"
    
    if curl -s -f "$GO_URL/api/health" >/dev/null 2>&1; then
        go_status="✅"
    fi
    
    if curl -s -f "$LARAVEL_URL" >/dev/null 2>&1; then
        laravel_status="✅"
    fi
    
    echo "Go: $go_status | Laravel: $laravel_status"
}

# Continuous monitoring
monitor() {
    print_info "Starting continuous monitoring (Ctrl+C to stop)..."
    echo ""
    
    while true; do
        echo -n "$(date '+%H:%M:%S') - "
        quick_check
        sleep 5
    done
}

# Main execution
case "${1:-status}" in
    "status"|"check")
        show_detailed_status
        ;;
    "quick")
        quick_check
        ;;
    "monitor")
        monitor
        ;;
    "help"|"-h"|"--help")
        echo "Usage: $0 [status|quick|monitor|help]"
        echo ""
        echo "Commands:"
        echo "  status   - Show detailed server health (default)"
        echo "  quick    - Quick status check"
        echo "  monitor  - Continuous monitoring"
        echo "  help     - Show this help"
        ;;
    *)
        print_error "Unknown command: $1"
        echo "Use '$0 help' for usage information"
        exit 1
        ;;
esac