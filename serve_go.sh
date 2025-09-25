#!/bin/zsh
# Start the Go API server with better error handling

set -e

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Navigate to go server directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)"
GO_SERVER_DIR="$SCRIPT_DIR/goserver"

cd "$GO_SERVER_DIR"

# Check if binary exists
if [[ ! -f "./goserver" ]]; then
    print_info "Go server binary not found. Building..."
    if make build; then
        print_success "Go server built successfully"
    else
        print_error "Failed to build Go server"
        exit 1
    fi
fi

# Start the server
print_info "Starting Go API server on port 8080..."
print_info "Press Ctrl+C to stop the server"

# Run with proper logging
./goserver 2>&1 | tee log.txt
