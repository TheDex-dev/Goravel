# Makefile for Pendataan IGD - Go API Migration

.PHONY: help build run test clean start deps fmt lint test-api setup-db status

# Variables
BINARY_NAME=goserver
MAIN_FILE=main.go

# Default target
help:
	@echo "🏥 Pendataan IGD - Go API Migration Commands"
	@echo "==========================================="
	@echo ""
	@echo "Development:"
	@echo "  build      - Build the application"
	@echo "  run        - Run the application"
	@echo "  start      - Build and run the application"
	@echo "  clean      - Clean build artifacts"
	@echo "  test       - Run Go tests"
	@echo "  deps       - Install/update dependencies"
	@echo "  fmt        - Format code"
	@echo "  lint       - Lint code (requires golangci-lint)"
	@echo ""
	@echo "Migration Testing:"
	@echo "  test-api   - Test API endpoints (requires server running)"
	@echo "  setup-db   - Setup PostgreSQL database"
	@echo "  status     - Show migration status and endpoints"
	@echo ""
	@echo "Production:"
	@echo "  build-prod - Build for production"

# Build the application
build:
	go build -o $(BINARY_NAME) $(MAIN_FILE)

# Run the application
run:
	go run $(MAIN_FILE)

# Build and run
start: build
	./$(BINARY_NAME)

# Run tests
test:
	go test -v ./...

# Clean build artifacts
clean:
	go clean
	rm -f $(BINARY_NAME)

# Download dependencies
deps:
	go mod download
	go mod tidy

# Format code
fmt:
	go fmt ./...

# Run linter (if golangci-lint is installed)
lint:
	@if command -v golangci-lint >/dev/null 2>&1; then \
		golangci-lint run; \
	else \
		echo "golangci-lint not installed. Install it with: go install github.com/golangci/golangci-lint/cmd/golangci-lint@latest"; \
	fi

# Run API tests (requires server to be running)
test-api:
	./test_api.sh

# Setup database (PostgreSQL) - Updated for Pendataan IGD migration
setup-db:
	@echo "🏥 Setting up PostgreSQL database for Pendataan IGD migration..."
	sudo -u postgres psql -c "CREATE DATABASE laravel_app;" || echo "Database already exists"
	sudo -u postgres psql -c "CREATE USER laravel_user WITH PASSWORD '';" || echo "User already exists"  
	sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE laravel_app TO laravel_user;" || true
	@echo "✅ Database setup completed! Run 'make start' to launch the API server."

# Show migration status and API endpoints
status:
	@echo "📊 Pendataan IGD - Laravel to Go Migration Status"
	@echo "=============================================="
	@echo ""
	@echo "✅ Phase 1 Implementation Complete:"
	@echo "   📋 Core Escort CRUD Operations"
	@echo "   🏥 Dashboard Statistics"
	@echo "   🖼️  Image Management (Base64)"
	@echo "   📱 QR Code Generation"
	@echo ""
	@echo "🌐 API Endpoints Available:"
	@echo "   POST   /api/escort              - Create escort"
	@echo "   GET    /api/escort              - List escorts (with filters)"
	@echo "   GET    /api/escort/{id}         - Get escort by ID"
	@echo "   PUT    /api/escort/{id}         - Update escort"
	@echo "   DELETE /api/escort/{id}         - Delete escort"
	@echo "   PATCH  /api/escort/{id}/status  - Update status"
	@echo "   GET    /api/dashboard/stats     - Dashboard statistics"
	@echo "   GET    /api/escort/{id}/image/base64    - Get image"
	@echo "   POST   /api/escort/{id}/image/base64    - Upload image"
	@echo "   GET    /api/qr-code/form        - Generate QR code"
	@echo ""
	@echo "🔧 Commands:"
	@echo "   make start      - Start the API server"
	@echo "   make test-api   - Test all endpoints"
	@echo "   make setup-db   - Setup PostgreSQL database"

# Build for production
build-prod:
	CGO_ENABLED=0 GOOS=linux go build -a -installsuffix cgo -ldflags '-w -s' -o $(BINARY_NAME) $(MAIN_FILE)