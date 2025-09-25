# Laravel to Go API Migration - Implementation Guide

This document explains how the Laravel application has been transformed to use the Go API as described in the migration documentation.

## 🎯 Migration Overview

The Laravel application now acts as a **proxy layer** that forwards API requests to the Go backend while maintaining:
- Laravel session management
- Authentication and authorization
- Backward compatibility
- Enhanced error handling and logging

## 🏗️ Architecture

```
[Frontend/Client] 
    ↓ HTTP Request
[Laravel API Routes] 
    ↓ Via GoApiService
[Go API Backend (Port 8080)]
    ↓ PostgreSQL Database
[Response back to client]
```

## 📁 New Files Added

### 1. GoApiService (`app/Services/GoApiService.php`)
HTTP client service that communicates with the Go API using Guzzle:
- `getEscorts()` - List escorts with filtering
- `createEscort()` - Create new escort
- `getEscort()` - Get single escort
- `updateEscort()` - Update escort
- `deleteEscort()` - Delete escort
- `updateEscortStatus()` - Update escort status
- `getDashboardStats()` - Get dashboard statistics
- `getImageBase64()` - Get image as base64
- `uploadImageBase64()` - Upload image as base64
- `generateQrCodePng()` - Generate QR code (PNG)
- `generateQrCodeJson()` - Generate QR code (JSON)

### 2. EscortGoApiController (`app/Http/Controllers/Api/EscortGoApiController.php`)
Proxy controller that forwards requests to Go API while maintaining Laravel session features:
- All CRUD operations for escorts
- Status management
- Image handling (base64)
- Dashboard statistics
- Session tracking and analytics

### 3. QrCodeGoController (`app/Http/Controllers/Api/QrCodeGoController.php`)
QR code generation proxy controller:
- `generateFormQrCode()` - GET method (returns PNG)
- `generateFormQrCodeJson()` - POST method (returns JSON)

### 4. ApiRoutingMiddleware (`app/Http/Middleware/ApiRoutingMiddleware.php`)
Intelligent routing middleware that can switch between Go API and Laravel:
- Header-based routing (`X-Use-Go-API: true/false`)
- Query parameter routing (`?use_go_api=true`)
- Environment-based configuration
- Auto-detection with health checks

## 🔧 Configuration

### Environment Variables (.env)
```bash
# Go API Configuration
GO_API_URL=http://localhost:8080
GO_API_TIMEOUT=30
GO_API_RETRY_ATTEMPTS=3
GO_API_ENABLED=true
GO_API_AUTO_DETECT=true
```

### Services Configuration (`config/services.php`)
```php
'go_api' => [
    'url' => env('GO_API_URL', 'http://localhost:8080'),
    'timeout' => env('GO_API_TIMEOUT', 30),
    'retry_attempts' => env('GO_API_RETRY_ATTEMPTS', 3),
],
```

## 🛣️ API Routes Structure

### Primary Routes (Go API)
All main API endpoints now route through the Go API:

```php
// Public routes
POST /api/escort                           → EscortGoApiController@store
GET  /api/session-stats                    → EscortGoApiController@getSessionStats
GET  /api/qr-code/form                     → QrCodeGoController@generateFormQrCode
POST /api/qr-code/form                     → QrCodeGoController@generateFormQrCodeJson

// Protected routes (require auth:sanctum)
GET    /api/escort                         → EscortGoApiController@index
GET    /api/escort/{id}                    → EscortGoApiController@show
PUT    /api/escort/{id}                    → EscortGoApiController@update
DELETE /api/escort/{id}                    → EscortGoApiController@destroy
PATCH  /api/escort/{id}/status             → EscortGoApiController@updateStatus
GET    /api/escort/{id}/image/base64       → EscortGoApiController@getImageBase64
POST   /api/escort/{id}/image/base64       → EscortGoApiController@uploadImageBase64
GET    /api/dashboard/stats                → EscortGoApiController@getDashboardStats
```

### Legacy Routes (Laravel Fallback)
Original Laravel implementation available under `/api/legacy/` prefix:

```php
// Legacy routes for backward compatibility
POST /api/legacy/escort                    → EscortApi@store
GET  /api/legacy/session-stats             → EscortApi@getSessionStats
GET  /api/legacy/qr-code/form              → EscortDataController@generateFormQrCode

// Protected legacy routes
GET    /api/legacy/escort                  → EscortApi@index
GET    /api/legacy/escort/{id}             → EscortApi@show
PUT    /api/legacy/escort/{id}             → EscortApi@update
DELETE /api/legacy/escort/{id}             → EscortApi@destroy
PATCH  /api/legacy/escort/{id}/status      → EscortApi@updateStatus
GET    /api/legacy/escort/{id}/image/base64 → EscortApi@getImageBase64
POST   /api/legacy/escort/{id}/image/base64 → EscortApi@uploadImageBase64
GET    /api/legacy/dashboard/stats         → EscortApi@getDashboardStats
```

## 🔀 Routing Control

### Method 1: Request Headers
Force specific backend selection:
```bash
# Use Go API
curl -H "X-Use-Go-API: true" http://localhost/api/escort

# Use Laravel
curl -H "X-Use-Go-API: false" http://localhost/api/escort
```

### Method 2: Query Parameters
For testing and debugging:
```bash
# Use Go API
curl "http://localhost/api/escort?use_go_api=true"

# Use Laravel
curl "http://localhost/api/escort?use_go_api=false"
```

### Method 3: Environment Configuration
```bash
# Enable Go API (default)
GO_API_ENABLED=true

# Disable Go API (use Laravel)
GO_API_ENABLED=false

# Auto-detect Go API availability
GO_API_AUTO_DETECT=true
```

## 📊 Response Format

All responses include routing information:

```json
{
  "status": "success",
  "message": "Data retrieved successfully",
  "data": { ... },
  "laravel_session_id": "abc123...",
  "laravel_meta": {
    "api_access_count": 5,
    "proxy_timestamp": "2025-09-25T10:30:00Z"
  }
}
```

Response headers:
```
X-API-Backend: go
X-Routing-Reason: env_enabled:true
X-Laravel-Session-ID: abc123...
```

## 🔍 Session Management Enhancement

Laravel maintains enhanced session tracking even when using Go API:

### Session Data Tracked:
- `api_access_count` - Number of API calls
- `api_submissions_count` - Number of escort submissions
- `api_updates_count` - Number of updates performed
- `api_status_updates_count` - Number of status changes
- `api_deletions_count` - Number of deletions
- `api_image_uploads_count` - Number of image uploads
- `recently_viewed_escorts` - Last 10 viewed escorts
- `recent_api_submissions` - Last 20 submissions
- `recent_status_updates` - Last 20 status updates

### Combined Session Stats Endpoint:
```bash
GET /api/session-stats
```

Returns both Laravel session data and Go API session statistics.

## 🚀 Getting Started

### 1. Ensure Go API is Running
```bash
cd /home/stolas/project0/goserver
go build
./goserver
# Server should be running on http://localhost:8080
```

### 2. Configure Laravel Environment
```bash
cd /home/stolas/project0/Pendataan_IGD
cp .env.example .env

# Add Go API configuration to .env
echo "GO_API_URL=http://localhost:8080" >> .env
echo "GO_API_ENABLED=true" >> .env
echo "GO_API_AUTO_DETECT=true" >> .env
```

### 3. Test the Integration
```bash
# Test Go API health
curl http://localhost:8080/api/health

# Test Laravel proxy to Go API
curl http://localhost/api/escort

# Test with explicit routing
curl -H "X-Use-Go-API: true" http://localhost/api/escort
```

## 🔧 Error Handling

The proxy implementation includes comprehensive error handling:

### Go API Unavailable
- Automatic fallback options available
- Detailed error logging
- Client receives informative error messages

### Network Issues
- Configurable timeout and retry settings
- Connection error tracking
- Graceful degradation

### Validation Errors
- Laravel validation runs before proxy call
- Go API validation errors are passed through
- Consistent error response format

## 📈 Benefits Achieved

1. **Performance**: Requests now served by compiled Go binary
2. **Scalability**: Better concurrent request handling
3. **Compatibility**: Existing Laravel features maintained
4. **Flexibility**: Can switch between backends
5. **Monitoring**: Enhanced logging and session tracking
6. **Fallback**: Legacy routes for backward compatibility

## 🧪 Testing Migration

### Test All Endpoints:
```bash
# Create escort
curl -X POST http://localhost/api/escort \
  -H "Content-Type: application/json" \
  -d '{"kategori_pengantar":"Polisi","nama_pengantar":"Test","jenis_kelamin":"Laki-laki","nomor_hp":"081234567890","plat_nomor":"B1234ABC","nama_pasien":"Test Patient"}'

# List escorts
curl http://localhost/api/escort

# Get single escort
curl http://localhost/api/escort/1

# Update escort
curl -X PUT http://localhost/api/escort/1 \
  -H "Content-Type: application/json" \
  -d '{"nama_pengantar":"Updated Name"}'

# Update status
curl -X PATCH http://localhost/api/escort/1/status \
  -H "Content-Type: application/json" \
  -d '{"status":"verified"}'

# Get QR code
curl http://localhost/api/qr-code/form -o qrcode.png

# Get dashboard stats
curl http://localhost/api/dashboard/stats
```

### Compare Legacy vs Go API:
```bash
# Go API (new)
curl http://localhost/api/escort

# Laravel API (legacy)
curl http://localhost/api/legacy/escort
```

## 🎯 Migration Completion

✅ **Phase 1: Core CRUD Operations** - ✅ COMPLETED
- All HIGH PRIORITY endpoints implemented
- All MEDIUM PRIORITY endpoints implemented  
- Base64 image handling migrated
- QR code generation migrated
- Dashboard statistics migrated

🔄 **Proxy Layer Benefits**:
- ✅ Zero downtime migration
- ✅ Gradual rollout capability
- ✅ A/B testing support
- ✅ Fallback safety net
- ✅ Enhanced monitoring

The Laravel application now successfully proxies requests to the Go API while maintaining all existing functionality and adding enhanced session management and routing flexibility.