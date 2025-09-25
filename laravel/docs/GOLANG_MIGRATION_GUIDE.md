# Laravel to Golang API Migration Guide

## Overview
This document outlines which endpoints from the Laravel Pendataan IGD application can be migrated to a Golang API backend. The analysis categorizes endpoints based on their complexity, dependencies, and suitability for migration.

## Database Schema

### Main Entity: Escorts
```sql
CREATE TABLE escorts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    kategori_pengantar ENUM('Polisi', 'Ambulans', 'Perorangan'),
    nama_pengantar VARCHAR(255),
    jenis_kelamin ENUM('Laki-laki', 'Perempuan'),
    nomor_hp VARCHAR(20),
    plat_nomor VARCHAR(20),
    nama_pasien VARCHAR(255),
    foto_pengantar VARCHAR(255) NULL,
    submission_id VARCHAR(255) NULL,
    submitted_from_ip VARCHAR(255) NULL,
    api_submission BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Migration Categories

### ✅ HIGH PRIORITY - Easy Migration (Stateless CRUD)

These endpoints are ideal candidates for migration to Golang as they are stateless, database-focused, and have minimal Laravel dependencies.

#### 1. Core Escort API Endpoints

| Endpoint | Method | Laravel Route | Controller Method | Description |
|----------|--------|---------------|-------------------|-------------|
| `/api/escort` | GET | `escort.index` | `EscortApi::index` | List escorts with filtering/pagination |
| `/api/escort` | POST | `escort.store` | `EscortApi::store` | Create new escort record |
| `/api/escort/{id}` | GET | `escort.show` | `EscortApi::show` | Get single escort record |
| `/api/escort/{id}` | PUT/PATCH | `escort.update` | `EscortApi::update` | Update escort record |
| `/api/escort/{id}` | DELETE | `escort.destroy` | `EscortApi::destroy` | Delete escort record |

**Migration Complexity**: Low
**Dependencies**: Database, File Storage
**Session Usage**: Minimal (only for tracking, can be removed)

#### 2. Status Management

| Endpoint | Method | Laravel Route | Controller Method | Description |
|----------|--------|---------------|-------------------|-------------|
| `/api/escort/{id}/status` | PATCH | `escort.updateStatus` | `EscortApi::updateStatus` | Update escort status |

**Migration Complexity**: Low
**Dependencies**: Database only

#### 3. Dashboard Statistics

| Endpoint | Method | Laravel Route | Controller Method | Description |
|----------|--------|---------------|-------------------|-------------|
| `/api/dashboard/stats` | GET | `dashboard.stats` | `EscortApi::getDashboardStats` | Get dashboard statistics |
| `/api/session-stats` | GET | `session-stats` | `EscortApi::getSessionStats` | Get session statistics |

**Migration Complexity**: Low-Medium
**Dependencies**: Database, Cache (optional)
**Notes**: Can be made stateless by removing session dependencies

### ✅ MEDIUM PRIORITY - Moderate Migration (File Handling)

#### 4. Image Management Endpoints

| Endpoint | Method | Laravel Route | Controller Method | Description |
|----------|--------|---------------|-------------------|-------------|
| `/api/escort/{id}/image/base64` | GET | `escort.getImageBase64` | `EscortApi::getImageBase64` | Get image as base64 |
| `/api/escort/{id}/image/base64` | POST | `escort.uploadImageBase64` | `EscortApi::uploadImageBase64` | Upload image as base64 |

**Migration Complexity**: Medium
**Dependencies**: File Storage, Image Processing Libraries
**Key Requirements**:
- Base64 encoding/decoding
- Image validation (MIME type, size limits)
- File storage management
- Error handling for corrupted images

#### 5. QR Code Generation

| Endpoint | Method | Laravel Route | Controller Method | Description |
|----------|--------|---------------|-------------------|-------------|
| `/api/qr-code/form` | GET | `qr.form` | `EscortDataController::generateFormQrCode` | Generate QR code for form |

**Migration Complexity**: Medium
**Dependencies**: QR Code library
**Notes**: Simple SVG/PNG generation endpoint

### 🟡 LOW PRIORITY - Complex Migration (Session-Heavy)

These endpoints have significant session dependencies and would require substantial refactoring.

#### 6. Authentication Endpoints

| Endpoint | Method | Laravel Route | Controller Method | Description |
|----------|--------|---------------|-------------------|-------------|
| `/api/sanctum/csrf-cookie` | GET | `auth.csrf` | `AuthApiController::csrfToken` | Get CSRF token |
| `/api/auth/login` | POST | `auth.login` | `AuthApiController::login` | Login with session |
| `/api/auth/logout` | POST | `auth.logout` | `AuthApiController::logout` | Logout |
| `/api/auth/check` | GET | `auth.check` | `AuthApiController::check` | Check auth status |
| `/api/auth/user` | GET | `auth.user` | `AuthApiController::user` | Get current user |
| `/api/auth/sanctum` | GET | `auth.sanctum` | `AuthApiController::sanctum` | Initialize sanctum session |

**Migration Complexity**: High
**Dependencies**: Session management, CSRF protection, Rate limiting
**Recommendation**: Replace with JWT/Token-based authentication

### ❌ NOT RECOMMENDED - Keep in Laravel

These endpoints are deeply integrated with Laravel's ecosystem and should remain in the original application.

#### 7. Web Form Endpoints (Blade Views)

| Endpoint | Method | Laravel Route | Controller Method | Description |
|----------|--------|---------------|-------------------|-------------|
| `/form` | GET | `form.index` | `EscortDataController::index` | Show form page |
| `/form/submit` | POST | `form.store` | `EscortDataController::store` | Handle form submission |
| `/dashboard` | GET | `dashboard` | `EscortDataController::dashboard` | Dashboard page |
| `/login` | GET/POST | `login` | `AuthController::*` | Login page |

**Reason**: Blade template rendering, session flash messages, CSRF protection

#### 8. File Export Endpoints

| Endpoint | Method | Laravel Route | Controller Method | Description |
|----------|--------|---------------|-------------------|-------------|
| `/dashboard/download/csv` | POST | `dashboard.download.csv` | `EscortDataController::downloadCsv` | Export CSV |
| `/dashboard/download/excel` | POST | `dashboard.download.excel` | `EscortDataController::downloadExcel` | Export Excel |

**Reason**: Complex Excel generation with Laravel Excel package

## Implementation Guidelines

### 1. Database Connection
```go
// Use MySQL driver for Go
import (
    "database/sql"
    _ "github.com/go-sql-driver/mysql"
)
```

### 2. Struct Definition
```go
type Escort struct {
    ID                 uint      `json:"id" db:"id"`
    Status             string    `json:"status" db:"status"`
    KategoriPengantar  string    `json:"kategori_pengantar" db:"kategori_pengantar"`
    NamaPengantar      string    `json:"nama_pengantar" db:"nama_pengantar"`
    JenisKelamin       string    `json:"jenis_kelamin" db:"jenis_kelamin"`
    NomorHP            string    `json:"nomor_hp" db:"nomor_hp"`
    PlatNomor          string    `json:"plat_nomor" db:"plat_nomor"`
    NamaPasien         string    `json:"nama_pasien" db:"nama_pasien"`
    FotoPengantar      *string   `json:"foto_pengantar" db:"foto_pengantar"`
    SubmissionID       *string   `json:"submission_id" db:"submission_id"`
    SubmittedFromIP    *string   `json:"submitted_from_ip" db:"submitted_from_ip"`
    APISubmission      bool      `json:"api_submission" db:"api_submission"`
    CreatedAt          time.Time `json:"created_at" db:"created_at"`
    UpdatedAt          time.Time `json:"updated_at" db:"updated_at"`
}
```

### 3. Validation Rules
```go
type CreateEscortRequest struct {
    KategoriPengantar string `json:"kategori_pengantar" validate:"required,oneof=Polisi Ambulans Perorangan"`
    NamaPengantar     string `json:"nama_pengantar" validate:"required,min=3,max=255"`
    JenisKelamin      string `json:"jenis_kelamin" validate:"required,oneof='Laki-laki' Perempuan"`
    NomorHP           string `json:"nomor_hp" validate:"required,min=10,max=20"`
    PlatNomor         string `json:"plat_nomor" validate:"required,min=3,max=20"`
    NamaPasien        string `json:"nama_pasien" validate:"required,min=3,max=255"`
    FotoPengantarB64  string `json:"foto_pengantar_base64" validate:"required"`
    Status            string `json:"status" validate:"omitempty,oneof=pending verified rejected"`
}
```

### 4. Image Processing Requirements
- **Supported formats**: JPEG, PNG, GIF
- **Size limit**: 2MB
- **Validation**: MIME type detection, file header validation
- **Storage**: File system or cloud storage (S3, GCS)
- **Base64**: Encoding/decoding with proper error handling

### 5. Response Format
```go
type APIResponse struct {
    Status  string      `json:"status"`
    Message string      `json:"message"`
    Data    interface{} `json:"data,omitempty"`
    Meta    *Meta       `json:"meta,omitempty"`
    Errors  interface{} `json:"errors,omitempty"`
}

type Meta struct {
    CurrentPage int `json:"current_page,omitempty"`
    TotalPages  int `json:"total_pages,omitempty"`
    PerPage     int `json:"per_page,omitempty"`
    Total       int `json:"total,omitempty"`
}
```

## Migration Strategy

### Phase 1: Core CRUD Operations
1. Implement basic escort CRUD endpoints
2. Set up database connection and models
3. Implement validation middleware
4. Basic error handling

### Phase 2: File Handling
1. Implement image upload/download endpoints
2. Base64 processing
3. File storage management
4. Image validation

### Phase 3: Advanced Features
1. Dashboard statistics
2. QR code generation
3. Status management
4. Caching layer (optional)

### Phase 4: Authentication (Optional)
1. Replace session-based auth with JWT
2. Implement rate limiting
3. User management endpoints

## Key Considerations

### 1. Session Management
- **Current**: Heavy reliance on PHP sessions for tracking
- **Recommendation**: Remove session dependencies or implement Redis-based session store
- **Impact**: Statistics and tracking features need to be redesigned

### 2. File Storage
- **Current**: Laravel Storage facade with local/public disk
- **Migration**: Implement file storage abstraction (local filesystem, S3, etc.)
- **Paths**: Maintain compatibility with existing file paths

### 3. Error Handling
- **Current**: Laravel's exception handling with localized messages
- **Migration**: Implement similar error response structure
- **Languages**: Currently supports Indonesian error messages

### 4. Validation
- **Current**: Laravel's validation rules with custom messages
- **Migration**: Use Go validation library (e.g., `go-playground/validator`)
- **Maintain**: Same validation rules and error messages

### 5. Logging
- **Current**: Laravel Log facade
- **Migration**: Implement structured logging (logrus, zap, etc.)
- **Maintain**: Same log levels and error tracking

### 6. Caching
- **Current**: Laravel Cache facade (likely file-based)
- **Migration**: Implement Redis/Memcached for caching
- **Usage**: Currently used for statistics and rate limiting

## Testing Strategy

### 1. API Compatibility Tests
Create test suites to ensure Go API responses match Laravel API responses:

```go
func TestEscortAPICompatibility(t *testing.T) {
    // Test that Go API returns same structure as Laravel API
    // Compare response schemas, status codes, error messages
}
```

### 2. Migration Validation
1. Set up both APIs in parallel
2. Mirror requests to both systems
3. Compare responses for consistency
4. Gradually migrate traffic

### 3. Load Testing
- Test performance improvements with Go implementation
- Ensure file upload handling is robust
- Validate concurrent request handling

## Recommended Go Libraries

### Core Framework
- **Fiber** or **Gin**: HTTP framework
- **GORM**: ORM for database operations
- **go-playground/validator**: Request validation

### File Handling
- **multipart**: Built-in for file uploads
- **image**: Built-in for image processing
- **base64**: Built-in for base64 encoding/decoding

### Utilities
- **logrus** or **zap**: Structured logging
- **redis**: Caching and session storage
- **testify**: Testing framework
- **viper**: Configuration management

## Conclusion

**Recommended Migration Order:**
1. Start with core CRUD endpoints (`escort.*`)
2. Add image handling endpoints
3. Implement dashboard statistics
4. Consider authentication replacement

**Expected Benefits:**
- Better performance for API endpoints
- Reduced server resource usage
- Improved concurrent request handling
- Simplified deployment (single binary)

**Keep in Laravel:**
- Web UI and Blade templates
- Complex Excel exports
- Session-based authentication (unless redesigned)
- Form rendering and CSRF protection

This migration strategy allows for a gradual transition while maintaining compatibility with existing clients and preserving Laravel's strengths for web UI components.