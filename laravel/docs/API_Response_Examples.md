# API Response Examples - Base64 Image Implementation

This document provides comprehensive examples of success and error responses for the Pendataan IGD API base64 image functionality.

## 1. Create Escort with Base64 Image

### ✅ Success Response (201 Created)

**Request:**
```json
POST /api/escort
{
    "kategori_pengantar": "Perorangan",
    "nama_pengantar": "John Doe",
    "jenis_kelamin": "Laki-laki",
    "nomor_hp": "081234567890",
    "plat_nomor": "B1234XYZ",
    "nama_pasien": "Jane Doe",
    "foto_pengantar_base64": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...",
    "foto_pengantar_info": {
        "name": "photo.jpg",
        "size": 2048,
        "type": "image/jpeg"
    }
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Data escort berhasil ditambahkan melalui API",
    "data": {
        "id": 123,
        "kategori_pengantar": "Perorangan",
        "nama_pengantar": "John Doe",
        "jenis_kelamin": "Laki-laki",
        "nomor_hp": "081234567890",
        "plat_nomor": "B1234XYZ",
        "nama_pasien": "Jane Doe",
        "foto_pengantar": "uploads/1726765200_66f1b550a8c9e_photo.jpg",
        "submission_id": "api_66f1b550a8c9e",
        "submitted_from_ip": "192.168.1.100",
        "api_submission": true,
        "status": "pending",
        "created_at": "2025-09-19T12:00:00.000000Z",
        "updated_at": "2025-09-19T12:00:00.000000Z"
    },
    "submission_id": "api_66f1b550a8c9e",
    "session_id": "session_abc123",
    "meta": {
        "api_submissions_count": 1,
        "timestamp": "2025-09-19T12:00:00.000000Z"
    }
}
```

### ❌ Validation Error - Invalid Base64 (500 Internal Server Error)

**Request:**
```json
POST /api/escort
{
    "kategori_pengantar": "Perorangan",
    "nama_pengantar": "John Doe",
    "jenis_kelamin": "Laki-laki",
    "nomor_hp": "081234567890",
    "plat_nomor": "B1234XYZ",
    "nama_pasien": "Jane Doe",
    "foto_pengantar_base64": "invalid-base64-string"
}
```

**Response:**
```json
{
    "status": "error",
    "message": "Gagal menyimpan data escort",
    "error": "Gagal memproses gambar: Format gambar tidak valid atau ukuran terlalu besar. Maksimal 2MB dan format yang didukung: JPEG, PNG, GIF.",
    "submission_id": "api_66f1b550a8c9e",
    "session_id": "session_abc123"
}
```

### ❌ Validation Error - Missing Required Fields (422 Unprocessable Entity)

**Request:**
```json
POST /api/escort
{
    "kategori_pengantar": "Perorangan",
    "nama_pengantar": "Jo",
    "nomor_hp": "123"
}
```

**Response:**
```json
{
    "status": "error",
    "message": "Validasi data gagal",
    "errors": {
        "jenis_kelamin": [
            "The jenis kelamin field is required."
        ],
        "plat_nomor": [
            "The plat nomor field is required."
        ],
        "nama_pasien": [
            "The nama pasien field is required."
        ],
        "foto_pengantar_base64": [
            "Foto pengantar wajib diisi."
        ],
        "nama_pengantar": [
            "Nama pengantar minimal 3 karakter."
        ],
        "nomor_hp": [
            "Nomor HP minimal 10 digit."
        ]
    },
    "submission_id": "api_66f1b550a8c9e",
    "session_id": "session_abc123"
}
```

## 2. Get Escorts with Base64 Images

### ✅ Success Response (200 OK)

**Request:**
```
GET /api/escort?include_images=base64&page=1&per_page=2
Authorization: Bearer {token}
```

**Response:**
```json
{
    "status": "success",
    "message": "Escorts retrieved successfully",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 123,
                "kategori_pengantar": "Perorangan",
                "nama_pengantar": "John Doe",
                "jenis_kelamin": "Laki-laki",
                "nomor_hp": "081234567890",
                "plat_nomor": "B1234XYZ",
                "nama_pasien": "Jane Doe",
                "foto_pengantar": "uploads/1726765200_66f1b550a8c9e_photo.jpg",
                "foto_pengantar_base64": {
                    "base64": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...",
                    "mime_type": "image/jpeg",
                    "size": 2048,
                    "file_path": "uploads/1726765200_66f1b550a8c9e_photo.jpg",
                    "storage_url": "/storage/uploads/1726765200_66f1b550a8c9e_photo.jpg"
                },
                "status": "pending",
                "created_at": "2025-09-19T12:00:00.000000Z",
                "updated_at": "2025-09-19T12:00:00.000000Z"
            },
            {
                "id": 124,
                "kategori_pengantar": "Ambulans",
                "nama_pengantar": "Jane Smith",
                "jenis_kelamin": "Perempuan",
                "nomor_hp": "081234567891",
                "plat_nomor": "B5678ABC",
                "nama_pasien": "Bob Smith",
                "foto_pengantar": "uploads/1726765300_66f1b5b4c7d2f_photo2.jpg",
                "foto_pengantar_base64": {
                    "base64": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==",
                    "mime_type": "image/png",
                    "size": 1024,
                    "file_path": "uploads/1726765300_66f1b5b4c7d2f_photo2.jpg",
                    "storage_url": "/storage/uploads/1726765300_66f1b5b4c7d2f_photo2.jpg"
                },
                "status": "verified",
                "created_at": "2025-09-19T12:05:00.000000Z",
                "updated_at": "2025-09-19T12:10:00.000000Z"
            }
        ],
        "first_page_url": "http://localhost:8000/api/escort?page=1",
        "from": 1,
        "last_page": 5,
        "last_page_url": "http://localhost:8000/api/escort?page=5",
        "next_page_url": "http://localhost:8000/api/escort?page=2",
        "path": "http://localhost:8000/api/escort",
        "per_page": 2,
        "prev_page_url": null,
        "to": 2,
        "total": 10
    },
    "session_id": "session_abc123",
    "meta": {
        "current_page": 1,
        "total_pages": 5,
        "per_page": 2,
        "total": 10,
        "api_access_count": 1
    }
}
```

### ❌ Unauthorized Error (401 Unauthorized)

**Request:**
```
GET /api/escort?include_images=base64
(No Authorization header)
```

**Response:**
```json
{
    "message": "Unauthenticated."
}
```

## 3. Get Image as Base64

### ✅ Success Response (200 OK)

**Request:**
```
GET /api/escort/123/image/base64
Authorization: Bearer {token}
```

**Response:**
```json
{
    "status": "success",
    "message": "Image converted to base64 successfully",
    "data": {
        "escort_id": 123,
        "nama_pengantar": "John Doe",
        "image_base64": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k=",
        "mime_type": "image/jpeg",
        "size": 2048,
        "storage_url": "/storage/uploads/1726765200_66f1b550a8c9e_photo.jpg"
    }
}
```

### ❌ Not Found - Escort Not Found (404 Not Found)

**Request:**
```
GET /api/escort/999/image/base64
Authorization: Bearer {token}
```

**Response:**
```json
{
    "status": "error",
    "message": "Escort tidak ditemukan"
}
```

### ❌ Not Found - No Image (404 Not Found)

**Request:**
```
GET /api/escort/125/image/base64
Authorization: Bearer {token}
```

**Response:**
```json
{
    "status": "error",
    "message": "Escort tidak memiliki foto"
}
```

## 4. Upload Image as Base64

### ✅ Success Response (200 OK)

**Request:**
```json
POST /api/escort/123/image/base64
Authorization: Bearer {token}
{
    "foto_pengantar_base64": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...",
    "foto_pengantar_info": {
        "name": "new_photo.jpg",
        "size": 2048,
        "type": "image/jpeg"
    }
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Gambar berhasil diupload via base64",
    "data": {
        "escort_id": 123,
        "image_path": "uploads/1726765400_66f1b618d5e3a_new_photo.jpg",
        "file_info": {
            "original_name": "new_photo.jpg",
            "stored_name": "1726765400_66f1b618d5e3a_new_photo.jpg",
            "size": 2048,
            "format": "jpeg",
            "mime_type": "image/jpeg"
        },
        "upload_id": "img_66f1b618d5e3a",
        "storage_url": "/storage/uploads/1726765400_66f1b618d5e3a_new_photo.jpg"
    }
}
```

### ❌ Validation Error - Invalid Base64 (500 Internal Server Error)

**Request:**
```json
POST /api/escort/123/image/base64
Authorization: Bearer {token}
{
    "foto_pengantar_base64": "invalid-base64-data"
}
```

**Response:**
```json
{
    "status": "error",
    "message": "Gagal mengupload gambar",
    "error": "Gagal memproses gambar base64."
}
```

### ❌ Validation Error - Missing Required Field (422 Unprocessable Entity)

**Request:**
```json
POST /api/escort/123/image/base64
Authorization: Bearer {token}
{
    "foto_pengantar_info": {
        "name": "photo.jpg"
    }
}
```

**Response:**
```json
{
    "status": "error",
    "message": "Validasi data gagal",
    "errors": {
        "foto_pengantar_base64": [
            "The foto pengantar base64 field is required."
        ]
    }
}
```

### ❌ Error - File Too Large (500 Internal Server Error)

**Request:**
```json
POST /api/escort/123/image/base64
Authorization: Bearer {token}
{
    "foto_pengantar_base64": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...[very large base64 string]",
    "foto_pengantar_info": {
        "name": "large_photo.jpg",
        "size": 5242880,
        "type": "image/jpeg"
    }
}
```

**Response:**
```json
{
    "status": "error",
    "message": "Gagal mengupload gambar",
    "error": "Gagal memproses gambar: Format gambar tidak valid atau ukuran terlalu besar. Maksimal 2MB dan format yang didukung: JPEG, PNG, GIF."
}
```

## 5. Authentication Responses

### ✅ Login Success (200 OK)

**Request:**
```json
POST /api/auth/login
{
    "email": "admin@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com",
            "email_verified_at": "2025-09-19T10:00:00.000000Z",
            "created_at": "2025-09-19T10:00:00.000000Z",
            "updated_at": "2025-09-19T10:00:00.000000Z"
        },
        "token": "1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yz",
        "token_type": "Bearer",
        "expires_in": null
    }
}
```

### ❌ Login Failed (401 Unauthorized)

**Request:**
```json
POST /api/auth/login
{
    "email": "admin@example.com",
    "password": "wrong_password"
}
```

**Response:**
```json
{
    "status": "error",
    "message": "Invalid credentials",
    "errors": {
        "email": [
            "The provided credentials are incorrect."
        ]
    }
}
```

## 6. Common Error Responses

### ❌ Method Not Allowed (405 Method Not Allowed)

**Request:**
```
POST /api/escort/123
Authorization: Bearer {token}
```

**Response:**
```json
{
    "message": "The POST method is not supported for this route. Supported methods: GET, HEAD, PUT, PATCH, DELETE."
}
```

### ❌ Internal Server Error (500 Internal Server Error)

**Response:**
```json
{
    "status": "error",
    "message": "An error occurred while processing your request",
    "error": "Detailed error message for debugging"
}
```

### ❌ Rate Limit Exceeded (429 Too Many Requests)

**Response:**
```json
{
    "message": "Too Many Attempts.",
    "retry_after": 60
}
```

## Response Field Explanations

### Success Response Fields
- **status**: Always "success" for successful requests
- **message**: Human-readable message describing the result
- **data**: The main response data (object or array)
- **meta**: Additional metadata (pagination, counts, etc.)
- **session_id**: Current session identifier
- **submission_id**: Unique identifier for submissions

### Error Response Fields
- **status**: Always "error" for failed requests
- **message**: Human-readable error message
- **error**: Detailed error description (for 500 errors)
- **errors**: Validation errors object (for 422 errors)

### Base64 Image Fields
- **base64**: Complete data URI with base64 encoded image
- **mime_type**: MIME type of the image (image/jpeg, image/png, etc.)
- **size**: File size in bytes
- **file_path**: Storage path relative to storage/app/public
- **storage_url**: Public URL for accessing the image
- **format**: Image format (jpeg, png, gif)

## HTTP Status Codes Used

- **200 OK**: Successful GET, PUT, PATCH requests
- **201 Created**: Successful POST requests that create resources
- **401 Unauthorized**: Authentication required or failed
- **404 Not Found**: Resource not found
- **422 Unprocessable Entity**: Validation errors
- **429 Too Many Requests**: Rate limiting
- **500 Internal Server Error**: Server-side errors