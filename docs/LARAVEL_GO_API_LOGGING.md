# Laravel to Go API Connection Logging

This document explains how the Laravel application logs its API connections to the Go server, providing proof of the integration working correctly.

## What's Been Added

### 1. Enhanced GoApiService Logging

The `GoApiService` class now includes comprehensive logging for all API calls between Laravel and the Go server:

- **Connection initialization logging** - Logs when the service is instantiated
- **Request/response logging** - Logs every API call with detailed information
- **Performance monitoring** - Tracks response times for each API call
- **Error tracking** - Enhanced error logging with status codes and timing

### 2. Logged Information

Each API call logs the following information:

```json
{
  "service": "GoApiService",
  "method": "GET|POST|PUT|DELETE|PATCH",
  "endpoint": "/api/endpoint",
  "full_url": "http://localhost:8080/api/endpoint",
  "timestamp": "2025-09-25T10:30:00.000Z",
  "result": "success|failed",
  "request_data": {...},
  "status_code": 200,
  "duration_ms": 45.23
}
```

### 3. Test Tools

#### Artisan Command
A new Artisan command for testing API connectivity:

```bash
# Run basic connection test
php artisan test:go-api-connection

# Run multiple test iterations
php artisan test:go-api-connection --count=5
```

#### Web Route Test
Access the web-based test at: `/test-go-connection`

This endpoint returns JSON with connection status and test results.

## How to Monitor API Connections

### 1. Real-time Log Monitoring

Watch Laravel logs in real-time to see API connections:

```bash
# Monitor all Go API connections
tail -f storage/logs/laravel.log | grep "Laravel->Go API"

# Monitor only successful connections
tail -f storage/logs/laravel.log | grep "Laravel->Go API Connection SUCCESS"

# Monitor only failed connections
tail -f storage/logs/laravel.log | grep "Laravel->Go API Connection FAILED"
```

### 2. Log File Analysis

Check the Laravel log file directly:

```bash
# View recent Go API logs
grep "Laravel->Go API" storage/logs/laravel.log | tail -20

# Check today's connections
grep "$(date '+%Y-%m-%d')" storage/logs/laravel.log | grep "Laravel->Go API"
```

### 3. Performance Analysis

Monitor API response times:

```bash
# Find slow API calls (over 1000ms)
grep "Laravel->Go API" storage/logs/laravel.log | grep -E '"duration_ms":[0-9]{4,}'

# Average response time analysis
grep "duration_ms" storage/logs/laravel.log | grep "Laravel->Go API Connection SUCCESS"
```

## Testing the Integration

### 1. Using the Artisan Command

```bash
# Basic test
php artisan test:go-api-connection

# Extended test with multiple calls
php artisan test:go-api-connection --count=10
```

### 2. Using the Web Interface

Visit: `http://your-laravel-app.local/test-go-connection`

### 3. Using Existing Controllers

The logging is automatically active on all existing API endpoints:

- **Escorts API**: `/api/escorts` (via EscortGoApiController)
- **QR Code API**: `/api/qr-code/*` (via QrCodeGoController)
- **Dashboard Stats**: Access through the dashboard

### 4. Manual Testing with cURL

Test the Laravel endpoints that use the Go API:

```bash
# Test health check through Laravel
curl -X GET "http://your-laravel-app.local/api/escorts"

# Test QR code generation
curl -X POST "http://your-laravel-app.local/api/qr-code/form" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","escort_type":"emergency"}'
```

## Log Examples

### Successful API Connection
```
[2025-09-25 10:30:15] local.INFO: Laravel->Go API Connection SUCCESS {"service":"GoApiService","method":"GET","endpoint":"/api/health","full_url":"http://localhost:8080/api/health","timestamp":"2025-09-25T10:30:15.123Z","result":"success","status_code":200,"duration_ms":45.67}
```

### Failed API Connection
```
[2025-09-25 10:30:20] local.ERROR: Laravel->Go API Connection FAILED {"service":"GoApiService","method":"GET","endpoint":"/api/escort","full_url":"http://localhost:8080/api/escort","timestamp":"2025-09-25T10:30:20.456Z","result":"failed","request_data":{"status":"active"},"status_code":null,"duration_ms":5000.12}
```

### Service Initialization
```
[2025-09-25 10:30:10] local.INFO: GoApiService initialized {"base_url":"http://localhost:8080","timestamp":"2025-09-25T10:30:10.000Z"}
```

## Troubleshooting

### Common Issues

1. **No logs appearing**: Ensure both Laravel and Go servers are running
2. **Connection timeouts**: Check if the Go server is accessible at the configured URL
3. **Permission errors**: Verify Laravel can write to the log directory

### Debugging Steps

1. Check Go server status:
   ```bash
   curl -X GET "http://localhost:8080/api/health"
   ```

2. Verify Laravel configuration:
   ```bash
   php artisan config:show services.go_api
   ```

3. Test with the Artisan command:
   ```bash
   php artisan test:go-api-connection
   ```

4. Check Laravel logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

## Configuration

The Go API URL is configured via:

1. **Environment variable**: `GO_API_URL=http://localhost:8080`
2. **Config file**: `config/services.php` under `go_api.url`

Make sure the URL points to your running Go server instance.

## Benefits

This logging system provides:

- **Proof of connectivity** - Clear evidence that Laravel is successfully communicating with Go
- **Performance monitoring** - Track API response times and identify bottlenecks  
- **Error tracking** - Immediate visibility into connection failures
- **Debugging support** - Detailed information for troubleshooting integration issues
- **Audit trail** - Complete history of all API interactions between the systems