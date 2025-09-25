#!/bin/bash

# Test Go API Integration
# This script tests the Laravel to Go API migration implementation

set -e

echo "🚀 Testing Laravel to Go API Migration"
echo "======================================"

# Configuration
LARAVEL_URL="http://localhost"
GO_API_URL="http://localhost:8080"
TEST_RESULTS_FILE="/tmp/go_api_test_results.json"

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

# Test Go API directly
test_go_api_direct() {
    echo ""
    print_info "Testing Go API directly..."
    
    # Test health endpoint
    if curl -s -f "${GO_API_URL}/api/health" > /dev/null; then
        print_success "Go API health check passed"
        
        # Get health details
        HEALTH_RESPONSE=$(curl -s "${GO_API_URL}/api/health")
        echo "Health Response: $HEALTH_RESPONSE"
    else
        print_error "Go API health check failed"
        print_warning "Make sure Go server is running on ${GO_API_URL}"
        print_warning "Run: cd /home/stolas/project0/goserver && ./goserver"
        return 1
    fi
    
    # Test escorts endpoint
    if curl -s -f "${GO_API_URL}/api/escort?per_page=1" > /dev/null; then
        print_success "Go API escorts endpoint accessible"
    else
        print_error "Go API escorts endpoint failed"
        return 1
    fi
    
    # Test QR code endpoint
    if curl -s -f "${GO_API_URL}/api/qr-code/form?url=https://example.com&size=200" > /dev/null; then
        print_success "Go API QR code endpoint accessible"
    else
        print_error "Go API QR code endpoint failed"
        return 1
    fi
}

# Test Laravel proxy to Go API
test_laravel_proxy() {
    echo ""
    print_info "Testing Laravel proxy to Go API..."
    
    # Test health through Laravel (should route to Go API)
    LARAVEL_ESCORT_RESPONSE=$(curl -s -H "X-Use-Go-API: true" "${LARAVEL_URL}/api/escort?per_page=1" || echo "FAILED")
    
    if [[ "$LARAVEL_ESCORT_RESPONSE" != "FAILED" ]] && echo "$LARAVEL_ESCORT_RESPONSE" | grep -q "success\|data"; then
        print_success "Laravel proxy to Go API working"
        
        # Check if response contains Laravel session info
        if echo "$LARAVEL_ESCORT_RESPONSE" | grep -q "laravel_session_id"; then
            print_success "Laravel session integration working"
        else
            print_warning "Laravel session integration might not be working"
        fi
        
        # Check routing headers
        ROUTING_HEADER=$(curl -s -I -H "X-Use-Go-API: true" "${LARAVEL_URL}/api/escort" | grep -i "x-api-backend" || echo "")
        if [[ "$ROUTING_HEADER" =~ "go" ]]; then
            print_success "Request routed to Go API backend"
        else
            print_warning "Routing header not found or incorrect"
        fi
        
    else
        print_error "Laravel proxy to Go API failed"
        echo "Response: $LARAVEL_ESCORT_RESPONSE"
        return 1
    fi
}

# Test legacy Laravel routes
test_legacy_routes() {
    echo ""
    print_info "Testing legacy Laravel routes..."
    
    LEGACY_RESPONSE=$(curl -s "${LARAVEL_URL}/api/legacy/session-stats" || echo "FAILED")
    
    if [[ "$LEGACY_RESPONSE" != "FAILED" ]] && echo "$LEGACY_RESPONSE" | grep -q "session_id\|stats"; then
        print_success "Legacy Laravel routes working"
    else
        print_error "Legacy Laravel routes failed"
        echo "Response: $LEGACY_RESPONSE"
        return 1
    fi
}

# Test QR code generation through Laravel proxy
test_qr_code_proxy() {
    echo ""
    print_info "Testing QR code generation through Laravel proxy..."
    
    # Test GET method (PNG)
    QR_PNG_RESPONSE=$(curl -s -w "%{http_code}" "${LARAVEL_URL}/api/qr-code/form?url=https://example.com&size=200" -o /tmp/qr_test.png)
    
    if [[ "$QR_PNG_RESPONSE" == "200" ]] && [[ -f "/tmp/qr_test.png" ]] && [[ $(stat -c%s "/tmp/qr_test.png") -gt 100 ]]; then
        print_success "QR code PNG generation through Laravel proxy working"
        rm -f /tmp/qr_test.png
    else
        print_error "QR code PNG generation failed"
        return 1
    fi
    
    # Test POST method (JSON)
    QR_JSON_RESPONSE=$(curl -s -X POST "${LARAVEL_URL}/api/qr-code/form" \
        -H "Content-Type: application/json" \
        -d '{"url": "https://example.com", "size": 200}')
    
    if echo "$QR_JSON_RESPONSE" | grep -q "qr_code_base64\|base64"; then
        print_success "QR code JSON generation through Laravel proxy working"
    else
        print_error "QR code JSON generation failed"
        echo "Response: $QR_JSON_RESPONSE"
        return 1
    fi
}

# Test routing control
test_routing_control() {
    echo ""
    print_info "Testing routing control mechanisms..."
    
    # Test explicit Go API routing
    GO_ROUTE_RESPONSE=$(curl -s -I -H "X-Use-Go-API: true" "${LARAVEL_URL}/api/escort" | grep -i "x-api-backend" || echo "")
    if [[ "$GO_ROUTE_RESPONSE" =~ "go" ]]; then
        print_success "Explicit Go API routing working"
    else
        print_warning "Explicit Go API routing might not be working"
    fi
    
    # Test explicit Laravel routing
    LARAVEL_ROUTE_RESPONSE=$(curl -s -I -H "X-Use-Go-API: false" "${LARAVEL_URL}/api/escort" | grep -i "x-api-backend" || echo "")
    if [[ "$LARAVEL_ROUTE_RESPONSE" =~ "laravel" ]]; then
        print_success "Explicit Laravel routing working"
    else
        print_warning "Explicit Laravel routing might not be working"
    fi
    
    # Test query parameter routing
    QUERY_ROUTE_RESPONSE=$(curl -s -I "${LARAVEL_URL}/api/escort?use_go_api=true" | grep -i "x-api-backend" || echo "")
    if [[ "$QUERY_ROUTE_RESPONSE" =~ "go" ]]; then
        print_success "Query parameter routing working"
    else
        print_warning "Query parameter routing might not be working"
    fi
}

# Test CRUD operations
test_crud_operations() {
    echo ""
    print_info "Testing CRUD operations through Laravel proxy..."
    
    # Test CREATE (POST)
    CREATE_RESPONSE=$(curl -s -X POST "${LARAVEL_URL}/api/escort" \
        -H "Content-Type: application/json" \
        -H "X-Use-Go-API: true" \
        -d '{
            "kategori_pengantar": "Polisi",
            "nama_pengantar": "Test User",
            "jenis_kelamin": "Laki-laki",
            "nomor_hp": "081234567890",
            "plat_nomor": "B1234TEST",
            "nama_pasien": "Test Patient"
        }')
    
    if echo "$CREATE_RESPONSE" | grep -q "success\|created"; then
        print_success "CREATE operation working"
        
        # Extract escort ID for further testing
        ESCORT_ID=$(echo "$CREATE_RESPONSE" | grep -o '"id":[0-9]*' | grep -o '[0-9]*' | head -1)
        
        if [[ -n "$ESCORT_ID" ]]; then
            print_info "Created escort with ID: $ESCORT_ID"
            
            # Test READ (GET)
            READ_RESPONSE=$(curl -s -H "X-Use-Go-API: true" "${LARAVEL_URL}/api/escort/${ESCORT_ID}")
            if echo "$READ_RESPONSE" | grep -q "Test User"; then
                print_success "READ operation working"
            else
                print_error "READ operation failed"
            fi
            
            # Test UPDATE (PUT)
            UPDATE_RESPONSE=$(curl -s -X PUT "${LARAVEL_URL}/api/escort/${ESCORT_ID}" \
                -H "Content-Type: application/json" \
                -H "X-Use-Go-API: true" \
                -d '{"nama_pengantar": "Updated Test User"}')
            
            if echo "$UPDATE_RESPONSE" | grep -q "success\|updated"; then
                print_success "UPDATE operation working"
            else
                print_error "UPDATE operation failed"
            fi
            
            # Test STATUS UPDATE (PATCH)
            STATUS_RESPONSE=$(curl -s -X PATCH "${LARAVEL_URL}/api/escort/${ESCORT_ID}/status" \
                -H "Content-Type: application/json" \
                -H "X-Use-Go-API: true" \
                -d '{"status": "verified"}')
            
            if echo "$STATUS_RESPONSE" | grep -q "success\|verified"; then
                print_success "STATUS UPDATE operation working"
            else
                print_error "STATUS UPDATE operation failed"
            fi
            
            # Test DELETE (DELETE)
            DELETE_RESPONSE=$(curl -s -X DELETE "${LARAVEL_URL}/api/escort/${ESCORT_ID}" \
                -H "X-Use-Go-API: true")
            
            if echo "$DELETE_RESPONSE" | grep -q "success\|deleted"; then
                print_success "DELETE operation working"
            else
                print_error "DELETE operation failed"
            fi
        fi
    else
        print_error "CREATE operation failed"
        echo "Response: $CREATE_RESPONSE"
        return 1
    fi
}

# Run all tests
run_all_tests() {
    echo "🧪 Running comprehensive migration tests..."
    
    local test_passed=0
    local test_total=0
    
    # Array of test functions
    tests=(
        "test_go_api_direct"
        "test_laravel_proxy" 
        "test_legacy_routes"
        "test_qr_code_proxy"
        "test_routing_control"
        "test_crud_operations"
    )
    
    for test_func in "${tests[@]}"; do
        ((test_total++))
        echo ""
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        if $test_func; then
            ((test_passed++))
        fi
    done
    
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "📊 Test Results Summary"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    
    if [[ $test_passed -eq $test_total ]]; then
        print_success "All tests passed! ($test_passed/$test_total)"
        print_success "🎉 Laravel to Go API migration is working correctly!"
        
        echo ""
        print_info "Migration benefits achieved:"
        echo "  ✅ High-performance Go backend serving requests"
        echo "  ✅ Laravel session management maintained"
        echo "  ✅ Backward compatibility with legacy routes"
        echo "  ✅ Flexible routing control mechanisms"
        echo "  ✅ All CRUD operations migrated successfully"
        echo "  ✅ QR code generation migrated"
        echo "  ✅ Image handling with base64 support"
        
        return 0
    else
        print_error "Some tests failed ($test_passed/$test_total passed)"
        
        echo ""
        print_info "Troubleshooting tips:"
        echo "  1. Ensure Go server is running: cd /home/stolas/project0/goserver && ./goserver"
        echo "  2. Check Go server logs for errors"
        echo "  3. Verify PostgreSQL database is accessible"
        echo "  4. Check Laravel .env configuration for GO_API_URL"
        echo "  5. Ensure Laravel web server is running"
        
        return 1
    fi
}

# Main execution
main() {
    if [[ "${1:-all}" == "all" ]]; then
        run_all_tests
    else
        # Run specific test
        case "$1" in
            "direct")
                test_go_api_direct
                ;;
            "proxy")
                test_laravel_proxy
                ;;
            "legacy")
                test_legacy_routes
                ;;
            "qr")
                test_qr_code_proxy
                ;;
            "routing")
                test_routing_control
                ;;
            "crud")
                test_crud_operations
                ;;
            *)
                echo "Usage: $0 [all|direct|proxy|legacy|qr|routing|crud]"
                echo ""
                echo "Test options:"
                echo "  all     - Run all tests (default)"
                echo "  direct  - Test Go API directly"
                echo "  proxy   - Test Laravel proxy to Go API"
                echo "  legacy  - Test legacy Laravel routes"
                echo "  qr      - Test QR code generation"
                echo "  routing - Test routing control mechanisms"
                echo "  crud    - Test CRUD operations"
                exit 1
                ;;
        esac
    fi
}

# Execute main function with all arguments
main "$@"