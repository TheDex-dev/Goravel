# Laravel Sanctum Session Authentication - Implementation Guide

This guide explains how Laravel Sanctum has been implemented in this project for session-based authentication, similar to the "Integrasi API on Laravel" approach.

## Overview

Laravel Sanctum has been configured to provide session-based authentication for SPAs (Single Page Applications) and mobile applications while maintaining the existing web authentication flow.

## Configuration

### 1. Sanctum Middleware Configuration

The following middleware has been enabled in `app/Http/Kernel.php`:

```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

### 2. Sanctum Configuration

The `config/sanctum.php` file is properly configured with:
- Stateful domains for session cookies
- Web guard for authentication
- CSRF middleware settings

## API Endpoints

### Authentication Endpoints

All authentication endpoints are available under `/api/auth/`:

- `GET /api/auth/sanctum` - Initialize Sanctum session
- `POST /api/auth/login` - Login with email/password
- `POST /api/auth/logout` - Logout
- `GET /api/auth/check` - Check authentication status
- `GET /api/auth/user` - Get current user
- `GET /api/sanctum/csrf-cookie` - Get CSRF token

### Protected Endpoints

Protected endpoints require authentication via `auth:sanctum` middleware:

- `GET /api/user` - Get authenticated user
- `GET /api/escort` - List escorts (protected)
- `POST /api/escort` - Create escort (public for form submissions)
- `PUT /api/escort/{id}` - Update escort (protected)
- `DELETE /api/escort/{id}` - Delete escort (protected)
- `GET /api/dashboard/stats` - Get dashboard statistics (protected)

## Frontend Implementation

### JavaScript Helper

A comprehensive JavaScript helper class `SanctumAuth` is available at `/js/sanctum-auth.js` and provides:

- Session initialization
- Login/logout functionality
- CSRF token management
- Authenticated API requests
- Event handling for authentication state changes

### Usage Example

```javascript
// Initialize Sanctum (done automatically)
await sanctumAuth.initializeSanctum();

// Login
try {
    const response = await sanctumAuth.login('user@example.com', 'password');
    console.log('Login successful:', response);
} catch (error) {
    console.error('Login failed:', error);
}

// Make authenticated API requests
try {
    const stats = await sanctumAuth.getDashboardStats();
    console.log('Dashboard stats:', stats);
} catch (error) {
    console.error('API request failed:', error);
}

// Check authentication status
const status = await sanctumAuth.checkAuthStatus();
console.log('Authenticated:', status.authenticated);
```

## Integration with Existing Views

### Layout Integration

The main layout (`resources/views/layout/app.blade.php`) includes:
- CSRF token meta tag
- Sanctum authentication script
- Automatic initialization for authenticated users

### Dashboard Integration

The dashboard includes demonstration buttons for:
- Loading stats via API
- Loading escorts via API
- Checking authentication status

## Authentication Flow

### 1. Session-Based Authentication

For users accessing via web interface:
1. User logs in through `/login` route
2. Laravel creates a web session
3. JavaScript initializes Sanctum for API calls
4. API requests use session cookies for authentication

### 2. API-Only Authentication

For external applications or SPAs:
1. Initialize Sanctum session via `/api/auth/sanctum`
2. Login via `/api/auth/login`
3. Use session cookies for subsequent API calls

## CSRF Protection

CSRF protection is handled automatically:
- Web forms use `@csrf` directive
- API requests include CSRF token in headers
- JavaScript helper manages token updates

## Session Management

Sessions are configured for:
- File-based storage (can be changed to database/redis)
- 120-minute lifetime (configurable)
- Automatic CSRF token refresh

## Error Handling

The implementation includes comprehensive error handling:
- Rate limiting for login attempts
- 401 response handling with automatic redirects
- Validation error responses
- Detailed logging for debugging

## Testing the Implementation

### Manual Testing

1. Login to the dashboard at `/login`
2. Use the "Sanctum API Test" section on the dashboard
3. Click "Check Auth Status" to verify session state
4. Click "Load Stats via API" to test authenticated endpoints

### API Testing with curl

```bash
# Get CSRF cookie first
curl -X GET http://localhost:8000/api/sanctum/csrf-cookie \
  -c cookies.txt

# Login
curl -X POST http://localhost:8000/api/auth/login \
  -b cookies.txt \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -d '{"email":"admin@igd.com","password":"admin123"}'

# Make authenticated request
curl -X GET http://localhost:8000/api/dashboard/stats \
  -b cookies.txt \
  -H "X-CSRF-TOKEN: your-csrf-token"
```

## Security Considerations

1. **CORS Configuration**: Ensure proper CORS settings for your domains
2. **HTTPS**: Use HTTPS in production for secure cookie transmission
3. **Session Security**: Configure secure session settings
4. **Rate Limiting**: Implemented for login attempts
5. **CSRF Protection**: Enabled for all state-changing operations

## Troubleshooting

### Common Issues

1. **CSRF Token Mismatch**: Ensure token is properly set in headers
2. **Session Not Persisting**: Check cookie settings and domain configuration
3. **CORS Issues**: Verify `sanctum.stateful` domains configuration
4. **Authentication Failing**: Check middleware order and configuration

### Debug Information

The implementation provides debug information via:
- Browser console logs
- Session statistics endpoint
- Authentication status checks
- Detailed error responses

## Migration from Web-Only to API Integration

If migrating from a web-only application:

1. Existing web authentication continues to work unchanged
2. API endpoints can be gradually introduced
3. Frontend can be enhanced with JavaScript helpers
4. Session-based authentication provides seamless integration

## Best Practices

1. **Initialize Sanctum** before making API calls
2. **Handle authentication events** for UI updates
3. **Use try-catch blocks** for API error handling
4. **Update CSRF tokens** when they change
5. **Check authentication status** before sensitive operations

This implementation provides a robust foundation for both traditional web applications and modern SPA architectures while maintaining security and ease of use.