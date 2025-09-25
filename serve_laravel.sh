#!/bin/zsh
# Start the Laravel server with better error handling

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

# Navigate to Laravel directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)"
LARAVEL_DIR="$SCRIPT_DIR/laravel"

cd "$LARAVEL_DIR"

# Check if vendor directory exists
if [[ ! -d "vendor" ]]; then
    print_error "Laravel dependencies not installed. Please run 'composer install'"
    exit 1
fi

# Check if .env file exists
if [[ ! -f ".env" ]]; then
    print_info ".env file not found. Creating from .env.example..."
    if [[ -f ".env.example" ]]; then
        cp .env.example .env
        print_info "Please configure your .env file and run 'php artisan key:generate'"
    else
        print_error "No .env.example file found. Please create .env manually"
        exit 1
    fi
fi

# Start the server
print_info "Starting Laravel server on port 8000..."
print_info "Press Ctrl+C to stop the server"

# Run with proper logging
php artisan serve --port=8000 2>&1 | tee storage/logs/serve.log