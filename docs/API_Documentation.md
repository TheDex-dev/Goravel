# Pendataan IGD API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication
The API uses Laravel Sanctum for authentication. Most endpoints require a Bearer token.

### Authentication Endpoints
- `GET /sanctum/csrf-cookie` - Get CSRF token
- `POST /auth/login` - Login and get access token
- `POST /auth/logout` - Logout and revoke token
- `GET /auth/check` - Check authentication status

## Escorts API

### Public Endpoints
- `POST /escort` - Create new escort (with base64 image support)
- `GET /session-stats` - Get session statistics

### Protected Endpoints (Require Authentication)
- `GET /escort` - List all escorts (with optional base64 images)
- `GET /escort/{id}` - Get single escort (includes base64 image)
- `PUT /escort/{id}` - Update escort (with base64 image support)
- `DELETE /escort/{id}` - Delete escort
- `PATCH /escort/{id}/status` - Update escort status

### Base64 Image Endpoints (Protected)
- `GET /escort/{id}/image/base64` - Get escort image as base64
- `POST /escort/{id}/image/base64` - Upload image as base64

### Dashboard Endpoint (Protected)
- `GET /dashboard/stats` - Get dashboard statistics

## Base64 Image Support

### Features
- ✅ Upload images as base64 in JSON requests
- ✅ Retrieve images as base64 in JSON responses
- ✅ Automatic image validation (format, size, content)
- ✅ Support for JPEG, PNG, JPG, GIF formats
- ✅ Maximum file size: 2MB
- ✅ Dedicated base64 image endpoints
- ✅ Session tracking and error handling

### Usage
Include base64 image data in requests:
```json
{
    "foto_pengantar_base64": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...",
    "foto_pengantar_info": {
        "name": "photo.jpg",
        "size": 2048,
        "type": "image/jpeg"
    }
}
```

### Get Images as Base64
- Add `?include_images=base64` to list requests
- Single escort requests automatically include base64 images
- Use dedicated `/image/base64` endpoints for image-only operations

## Response Format
All endpoints return JSON in this format:
```json
{
    "status": "success|error",
    "message": "Description",
    "data": {},
    "meta": {}
}
```

## Error Handling
- `422` - Validation errors
- `404` - Resource not found
- `401` - Unauthorized
- `500` - Server error

## Package Used
- `melihovv/base64-image-decoder` - For reliable base64 image processing

For detailed documentation and examples, see:
- `docs/Base64_Image_Implementation.md`
- `docs/Pendataan_IGD_API_Collection.postman_collection.json`