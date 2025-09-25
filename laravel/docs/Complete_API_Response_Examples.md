# Complete API Response Examples - All Endpoints

This document provides comprehensive examples for ALL API endpoints in the Pendataan IGD system.

## Authentication Endpoints

### 1. Get CSRF Token

#### ✅ Success Response (200 OK)
**Request:**
```
GET /api/sanctum/csrf-cookie
```

**Response:**
```
Set-Cookie: XSRF-TOKEN=eyJpdiI6Im...; path=/; samesite=lax
Set-Cookie: laravel_session=eyJpdiI6Im...; path=/; httponly; samesite=lax
```

### 2. Login

#### ✅ Success Response (200 OK)
**Request:**
```json
POST /api/auth/login
{
    "email": "admin@igd.com",
    "password": "password123"
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
            "name": "IGD Administrator",
            "email": "admin@igd.com",
            "email_verified_at": "2025-09-19T10:00:00.000000Z",
            "created_at": "2025-09-19T09:00:00.000000Z",
            "updated_at": "2025-09-19T10:00:00.000000Z"
        },
        "token": "1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yz567",
        "token_type": "Bearer",
        "expires_in": null
    }
}
```

#### ❌ Invalid Credentials (401 Unauthorized)
**Request:**
```json
POST /api/auth/login
{
    "email": "admin@igd.com",
    "password": "wrongpassword"
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

#### ❌ Validation Error (422 Unprocessable Entity)
**Request:**
```json
POST /api/auth/login
{
    "email": "invalid-email",
    "password": ""
}
```

**Response:**
```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "email": [
            "The email must be a valid email address."
        ],
        "password": [
            "The password field is required."
        ]
    }
}
```

### 3. Check Authentication Status

#### ✅ Authenticated Response (200 OK)
**Request:**
```
GET /api/auth/check
Authorization: Bearer {token}
```

**Response:**
```json
{
    "status": "success",
    "message": "User is authenticated",
    "data": {
        "authenticated": true,
        "user": {
            "id": 1,
            "name": "IGD Administrator",
            "email": "admin@igd.com",
            "created_at": "2025-09-19T09:00:00.000000Z"
        }
    }
}
```

#### ❌ Not Authenticated (401 Unauthorized)
**Request:**
```
GET /api/auth/check
(No Authorization header)
```

**Response:**
```json
{
    "message": "Unauthenticated."
}
```

### 4. Logout

#### ✅ Success Response (200 OK)
**Request:**
```
POST /api/auth/logout
Authorization: Bearer {token}
```

**Response:**
```json
{
    "status": "success",
    "message": "Successfully logged out",
    "data": {
        "logged_out_at": "2025-09-19T14:30:00.000000Z"
    }
}
```

#### ❌ Invalid Token (401 Unauthorized)
**Request:**
```
POST /api/auth/logout
Authorization: Bearer invalid_token
```

**Response:**
```json
{
    "message": "Unauthenticated."
}
```

## Escort Management Endpoints

### 5. Get All Escorts (Protected)

#### ✅ Success Response - No Filters (200 OK)
**Request:**
```
GET /api/escort?page=1&per_page=5
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
                "status": "verified",
                "created_at": "2025-09-19T12:05:00.000000Z",
                "updated_at": "2025-09-19T12:10:00.000000Z"
            }
        ],
        "first_page_url": "http://localhost:8000/api/escort?page=1",
        "from": 1,
        "last_page": 15,
        "last_page_url": "http://localhost:8000/api/escort?page=15",
        "next_page_url": "http://localhost:8000/api/escort?page=2",
        "path": "http://localhost:8000/api/escort",
        "per_page": 5,
        "prev_page_url": null,
        "to": 5,
        "total": 73
    },
    "session_id": "session_abc123",
    "meta": {
        "current_page": 1,
        "total_pages": 15,
        "per_page": 5,
        "total": 73,
        "api_access_count": 5
    }
}
```

#### ✅ Success Response - With Filters (200 OK)
**Request:**
```
GET /api/escort?kategori=Polisi&status=verified&search=John
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
                "id": 150,
                "kategori_pengantar": "Polisi",
                "nama_pengantar": "John Officer",
                "jenis_kelamin": "Laki-laki",
                "nomor_hp": "081234567899",
                "plat_nomor": "POL1234",
                "nama_pasien": "Emergency Patient",
                "foto_pengantar": "uploads/1726765400_66f1b618d5e3a_police.jpg",
                "status": "verified",
                "created_at": "2025-09-19T13:00:00.000000Z",
                "updated_at": "2025-09-19T13:15:00.000000Z"
            }
        ],
        "first_page_url": "http://localhost:8000/api/escort?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http://localhost:8000/api/escort?page=1",
        "next_page_url": null,
        "path": "http://localhost:8000/api/escort",
        "per_page": 15,
        "prev_page_url": null,
        "to": 1,
        "total": 1
    },
    "session_id": "session_abc123",
    "meta": {
        "current_page": 1,
        "total_pages": 1,
        "per_page": 15,
        "total": 1,
        "api_access_count": 6
    }
}
```

### 6. Get Single Escort

#### ✅ Success Response (200 OK)
**Request:**
```
GET /api/escort/123
Authorization: Bearer {token}
```

**Response:**
```json
{
    "status": "success",
    "message": "Escort retrieved successfully",
    "data": {
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
        "submission_id": "api_66f1b550a8c9e",
        "submitted_from_ip": "192.168.1.100",
        "api_submission": true,
        "status": "pending",
        "created_at": "2025-09-19T12:00:00.000000Z",
        "updated_at": "2025-09-19T12:00:00.000000Z"
    },
    "session_id": "session_abc123",
    "meta": {
        "view_count": 3,
        "last_viewed": "2025-09-19T14:30:00.000000Z",
        "has_base64_image": true
    }
}
```

#### ❌ Not Found (404 Not Found)
**Request:**
```
GET /api/escort/999999
Authorization: Bearer {token}
```

**Response:**
```json
{
    "status": "error",
    "message": "Data escort tidak ditemukan",
    "session_id": "session_abc123"
}
```

### 7. Update Escort

#### ✅ Success Response (200 OK)
**Request:**
```json
PUT /api/escort/123
Authorization: Bearer {token}
{
    "nama_pengantar": "John Doe Updated",
    "nomor_hp": "081234567899",
    "status": "verified"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Data escort berhasil diperbarui",
    "data": {
        "id": 123,
        "kategori_pengantar": "Perorangan",
        "nama_pengantar": "John Doe Updated",
        "jenis_kelamin": "Laki-laki",
        "nomor_hp": "081234567899",
        "plat_nomor": "B1234XYZ",
        "nama_pasien": "Jane Doe",
        "foto_pengantar": "uploads/1726765200_66f1b550a8c9e_photo.jpg",
        "status": "verified",
        "created_at": "2025-09-19T12:00:00.000000Z",
        "updated_at": "2025-09-19T14:45:00.000000Z"
    },
    "update_id": "upd_66f1b618d5e3a",
    "session_id": "session_abc123",
    "meta": {
        "updated_fields": ["nama_pengantar", "nomor_hp", "status"],
        "api_updates_count": 2
    }
}
```

#### ❌ Validation Error (422 Unprocessable Entity)
**Request:**
```json
PUT /api/escort/123
Authorization: Bearer {token}
{
    "nama_pengantar": "Jo",
    "nomor_hp": "123",
    "status": "invalid_status"
}
```

**Response:**
```json
{
    "status": "error",
    "message": "Validasi data gagal",
    "errors": {
        "nama_pengantar": [
            "Nama pengantar minimal 3 karakter."
        ],
        "nomor_hp": [
            "Nomor HP minimal 10 digit."
        ],
        "status": [
            "The selected status is invalid."
        ]
    },
    "session_id": "session_abc123"
}
```

### 8. Update Escort Status

#### ✅ Success Response (200 OK)
**Request:**
```json
PATCH /api/escort/123/status
Authorization: Bearer {token}
{
    "status": "verified"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Status escort berhasil diperbarui",
    "data": {
        "escort_id": 123,
        "old_status": "pending",
        "new_status": "verified",
        "status_display": "Terverifikasi",
        "badge_class": "badge-success"
    },
    "update_id": "status_66f1b618d5e3a",
    "session_id": "session_abc123",
    "meta": {
        "api_status_updates_count": 5,
        "timestamp": "2025-09-19T14:50:00.000000Z"
    }
}
```

#### ❌ Invalid Status (422 Unprocessable Entity)
**Request:**
```json
PATCH /api/escort/123/status
Authorization: Bearer {token}
{
    "status": "invalid_status"
}
```

**Response:**
```json
{
    "status": "error",
    "message": "Validasi status gagal",
    "errors": {
        "status": [
            "The selected status is invalid."
        ]
    },
    "session_id": "session_abc123"
}
```

### 9. Delete Escort

#### ✅ Success Response (200 OK)
**Request:**
```
DELETE /api/escort/123
Authorization: Bearer {token}
```

**Response:**
```json
{
    "status": "success",
    "message": "Data escort berhasil dihapus",
    "delete_id": "del_66f1b618d5e3a",
    "session_id": "session_abc123",
    "meta": {
        "api_deletions_count": 3
    }
}
```

#### ❌ Not Found (404 Not Found)
**Request:**
```
DELETE /api/escort/999999
Authorization: Bearer {token}
```

**Response:**
```json
{
    "status": "error",
    "message": "Data escort tidak ditemukan",
    "session_id": "session_abc123"
}
```

## Session & Statistics Endpoints

### 10. Get Session Stats (Public)

#### ✅ Success Response (200 OK)
**Request:**
```
GET /api/session-stats
```

**Response:**
```json
{
    "status": "success",
    "session_id": "session_abc123",
    "stats": {
        "api_access_count": 15,
        "api_submissions_count": 3,
        "api_updates_count": 2,
        "api_status_updates_count": 5,
        "api_deletions_count": 1,
        "api_failed_lookups": 2,
        "last_accessed": "2025-09-19T14:55:00.000000Z",
        "last_result_count": 5,
        "last_total": 73
    },
    "recent_activity": {
        "submissions": [
            {
                "id": 125,
                "submission_id": "api_66f1b550a8c9e",
                "nama_pengantar": "Alice Johnson",
                "nama_pasien": "Bob Johnson",
                "kategori": "Perorangan",
                "submitted_at": "2025-09-19T14:30:00.000000Z",
                "ip": "192.168.1.105"
            }
        ],
        "viewed": [
            {
                "id": 123,
                "nama_pengantar": "John Doe",
                "nama_pasien": "Jane Doe",
                "viewed_at": "2025-09-19T14:55:00.000000Z"
            }
        ],
        "deleted": [
            {
                "id": 120,
                "nama_pengantar": "Old Record",
                "nama_pasien": "Old Patient",
                "deleted_at": "2025-09-19T14:20:00.000000Z",
                "delete_id": "del_66f1b550a8c9e"
            }
        ],
        "status_updates": [
            {
                "escort_id": 123,
                "nama_pengantar": "John Doe",
                "old_status": "pending",
                "new_status": "verified",
                "updated_at": "2025-09-19T14:50:00.000000Z",
                "update_id": "status_66f1b618d5e3a",
                "ip": "192.168.1.100"
            }
        ]
    }
}
```

### 11. Get Dashboard Stats (Protected)

#### ✅ Success Response (200 OK)
**Request:**
```
GET /api/dashboard/stats
Authorization: Bearer {token}
```

**Response:**
```json
{
    "status": "success",
    "message": "Dashboard stats retrieved successfully",
    "data": {
        "total_escorts": 73,
        "today_submissions": 8,
        "this_week_submissions": 25,
        "this_month_submissions": 73,
        "pending_count": 15,
        "verified_count": 45,
        "rejected_count": 13,
        "by_category": {
            "Perorangan": 50,
            "Ambulans": 15,
            "Polisi": 8
        },
        "by_status": {
            "pending": 15,
            "verified": 45,
            "rejected": 13
        },
        "recent_submissions": [
            {
                "id": 125,
                "nama_pengantar": "Alice Johnson",
                "nama_pasien": "Bob Johnson",
                "kategori_pengantar": "Perorangan",
                "status": "pending",
                "created_at": "2025-09-19T14:30:00.000000Z"
            },
            {
                "id": 124,
                "nama_pengantar": "Jane Smith",
                "nama_pasien": "Bob Smith",
                "kategori_pengantar": "Ambulans",
                "status": "verified",
                "created_at": "2025-09-19T12:05:00.000000Z"
            }
        ],
        "session_info": {
            "session_id": "session_abc123",
            "api_access_count": 15,
            "user_submissions": 3,
            "status_updates": 5,
            "last_accessed": "2025-09-19T14:55:00.000000Z"
        }
    }
}
```

#### ❌ Server Error (500 Internal Server Error)
**Request:**
```
GET /api/dashboard/stats
Authorization: Bearer {token}
```

**Response:**
```json
{
    "status": "error",
    "message": "Failed to retrieve dashboard stats",
    "error": "Database connection error"
}
```

## Common Error Responses

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

### ❌ Route Not Found (404 Not Found)
**Request:**
```
GET /api/nonexistent-endpoint
```

**Response:**
```json
{
    "message": "Route not found."
}
```

### ❌ Rate Limit Exceeded (429 Too Many Requests)
**Request:**
```
POST /api/escort
(Too many requests in short time)
```

**Response:**
```json
{
    "message": "Too Many Attempts.",
    "retry_after": 60
}
```

### ❌ CSRF Token Mismatch (419 Page Expired)
**Request:**
```
POST /api/escort
(Invalid or missing CSRF token for session-based requests)
```

**Response:**
```json
{
    "message": "CSRF token mismatch"
}
```

### ❌ Payload Too Large (413 Payload Too Large)
**Request:**
```
POST /api/escort
(Request body larger than server limit)
```

**Response:**
```json
{
    "message": "The payload is too large"
}
```

### ❌ Unsupported Media Type (415 Unsupported Media Type)
**Request:**
```
POST /api/escort
Content-Type: text/plain
```

**Response:**
```json
{
    "message": "The request content type is not supported"
}
```

## Response Status Code Summary

| Status Code | Description | When It Occurs |
|-------------|-------------|----------------|
| 200 | OK | Successful GET, PUT, PATCH requests |
| 201 | Created | Successful POST requests that create resources |
| 401 | Unauthorized | Missing or invalid authentication |
| 404 | Not Found | Resource doesn't exist |
| 405 | Method Not Allowed | Wrong HTTP method for endpoint |
| 413 | Payload Too Large | Request body too large |
| 415 | Unsupported Media Type | Wrong Content-Type header |
| 419 | Page Expired | CSRF token issues |
| 422 | Unprocessable Entity | Validation errors |
| 429 | Too Many Requests | Rate limiting |
| 500 | Internal Server Error | Server-side errors |

## Field Validation Rules Summary

### Escort Fields
- **kategori_pengantar**: Required, must be one of: Polisi, Ambulans, Perorangan
- **nama_pengantar**: Required, string, 3-255 characters
- **jenis_kelamin**: Required, must be: Laki-laki or Perempuan
- **nomor_hp**: Required, string, 10-20 characters, numeric format
- **plat_nomor**: Required, string, 3-20 characters
- **nama_pasien**: Required, string, 3-255 characters
- **foto_pengantar_base64**: Required for creation, must be valid base64 image
- **status**: Optional, must be one of: pending, verified, rejected

### Authentication Fields
- **email**: Required, valid email format
- **password**: Required, minimum length varies by configuration