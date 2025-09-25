# Pendataan IGD - Go API Migration

This project implements the **Phase 1: Core CRUD Operations** migration from Laravel to Go as described in the Golang Migration Guide. It focuses on migrating the high-priority, stateless CRUD endpoints for the Pendataan IGD (Emergency Department Patient Registration) system.

## 🎯 Migration Goals Achieved

Following the migration guide, this implementation covers:

### ✅ HIGH PRIORITY - Implemented
- **Core Escort API Endpoints**: Complete CRUD operations for escort records
- **Status Management**: Update escort verification status
- **Dashboard Statistics**: Real-time statistics and analytics
- **Image Management**: Base64 image upload/download (MEDIUM PRIORITY completed)
- **QR Code Generation**: QR code generation for forms

### 📋 API Endpoints Implemented

| Endpoint | Method | Description | Status |
|----------|--------|-------------|---------|
| `GET /api/escort` | GET | List escorts with filtering/pagination | ✅ |
| `POST /api/escort` | POST | Create new escort record | ✅ |
| `GET /api/escort/{id}` | GET | Get single escort record | ✅ |
| `PUT /api/escort/{id}` | PUT/PATCH | Update escort record | ✅ |
| `DELETE /api/escort/{id}` | DELETE | Delete escort record | ✅ |
| `PATCH /api/escort/{id}/status` | PATCH | Update escort status | ✅ |
| `GET /api/dashboard/stats` | GET | Get dashboard statistics | ✅ |
| `GET /api/escort/{id}/image/base64` | GET | Get image as base64 | ✅ |
| `POST /api/escort/{id}/image/base64` | POST | Upload image as base64 | ✅ |
| `GET /api/qr-code/form` | GET | Generate QR code (PNG) | ✅ |
| `POST /api/qr-code/form` | POST | Generate QR code (JSON) | ✅ |

## 🏗️ Architecture

### Project Structure
```
goserver/
├── main.go                    # Application entry point
├── models/                    # Data models and request/response structures
│   └── escort.go
├── services/                  # Business logic layer
│   └── escort_service.go
├── handlers/                  # HTTP request handlers
│   ├── escort_handler.go
│   └── qr_handler.go
├── database/                  # Database connection and migrations
│   ├── database.go
│   └── migrations/
│       ├── 001_create_users_table.sql
│       ├── 002_create_escorts_table.sql
│       └── sample_data.sql
├── middleware/                # HTTP middleware
│   └── middleware.go
└── storage/                   # File storage (created at runtime)
    └── uploads/
```

### Technology Stack
- **Framework**: Gin (HTTP web framework)
- **Database**: PostgreSQL with pgx driver
- **Image Processing**: Base64 encoding/decoding
- **QR Codes**: go-qrcode library
- **Validation**: go-playground/validator
- **Configuration**: godotenv (.env file)

## 🚀 Getting Started

### Prerequisites
- Go 1.25.1+
- PostgreSQL 13+

### Installation

1. **Clone and setup the project**:
   ```bash
   cd /home/stolas/project0/goserver
   go mod download
   ```

2. **Configure environment**:
   The `.env` file is already configured for PostgreSQL:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=laravel_app
   DB_USERNAME=laravel_user
   DB_PASSWORD=
   ```

3. **Setup PostgreSQL database**:
   ```bash
   # Create database and user (if not exists)
   sudo -u postgres psql -c "CREATE DATABASE laravel_app;"
   sudo -u postgres psql -c "CREATE USER laravel_user WITH PASSWORD '';"
   sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE laravel_app TO laravel_user;"
   ```

4. **Build and run**:
   ```bash
   go build
   ./goserver
   ```

   The server will:
   - Connect to PostgreSQL
   - Run automatic migrations (creates tables and indexes)
   - Start listening on port 8080

## 📝 Database Schema

### Escorts Table
The main entity following the migration guide specifications:

```sql
CREATE TABLE escorts (
    id BIGSERIAL PRIMARY KEY,
    status VARCHAR(20) CHECK (status IN ('pending', 'verified', 'rejected')) DEFAULT 'pending',
    kategori_pengantar VARCHAR(20) CHECK (kategori_pengantar IN ('Polisi', 'Ambulans', 'Perorangan')),
    nama_pengantar VARCHAR(255) NOT NULL,
    jenis_kelamin VARCHAR(20) CHECK (jenis_kelamin IN ('Laki-laki', 'Perempuan')),
    nomor_hp VARCHAR(20) NOT NULL,
    plat_nomor VARCHAR(20) NOT NULL,
    nama_pasien VARCHAR(255) NOT NULL,
    foto_pengantar VARCHAR(255) NULL,
    submission_id VARCHAR(255) NULL,
    submitted_from_ip VARCHAR(255) NULL,
    api_submission BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW()
);
```

## 🔧 API Usage Examples

### 1. Create New Escort
```bash
curl -X POST http://localhost:8080/api/escort \
  -H "Content-Type: application/json" \
  -d '{
    "kategori_pengantar": "Polisi",
    "nama_pengantar": "Budi Santoso",
    "jenis_kelamin": "Laki-laki",
    "nomor_hp": "081234567890",
    "plat_nomor": "B1234ABC",
    "nama_pasien": "Siti Aminah",
    "foto_pengantar_base64": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAA..."
  }'
```

### 2. List Escorts with Filtering
```bash
# Get all escorts
curl http://localhost:8080/api/escort

# Filter by status and category
curl "http://localhost:8080/api/escort?status=pending&kategori_pengantar=Polisi&page=1&per_page=10"

# Search by name or license plate
curl "http://localhost:8080/api/escort?search=Budi&sort_by=created_at&sort_order=desc"
```

### 3. Update Escort Status
```bash
curl -X PATCH http://localhost:8080/api/escort/1/status \
  -H "Content-Type: application/json" \
  -d '{"status": "verified"}'
```

### 4. Get Dashboard Statistics
```bash
curl http://localhost:8080/api/dashboard/stats
```

### 5. Generate QR Code
```bash
# Get QR code as PNG image
curl "http://localhost:8080/api/qr-code/form?url=https://example.com/form&size=256" \
  --output qr-code.png

# Get QR code as JSON (base64)
curl -X POST http://localhost:8080/api/qr-code/form \
  -H "Content-Type: application/json" \
  -d '{"url": "https://example.com/form", "size": 256}'
```

## 📊 Response Format

All endpoints follow a consistent JSON response format:

```json
{
  "status": "success",
  "message": "Operation completed successfully",
  "data": { ... },
  "meta": {
    "current_page": 1,
    "total_pages": 5,
    "per_page": 10,
    "total": 48
  }
}
```

Error responses:
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "nama_pengantar": "nama_pengantar is required",
    "nomor_hp": "nomor_hp must be at least 10 characters"
  }
}
```

## 🔍 Validation Rules

Following the migration guide specifications:

- `kategori_pengantar`: Required, must be one of: Polisi, Ambulans, Perorangan
- `nama_pengantar`: Required, 3-255 characters
- `jenis_kelamin`: Required, must be one of: Laki-laki, Perempuan
- `nomor_hp`: Required, 10-20 characters
- `plat_nomor`: Required, 3-20 characters
- `nama_pasien`: Required, 3-255 characters
- `foto_pengantar_base64`: Optional, valid base64 image data
- `status`: Optional, must be one of: pending, verified, rejected

## 📁 File Storage

Images are stored in the `storage/uploads/` directory with unique filenames:
- Supported formats: JPEG, PNG, GIF
- Maximum size: 2MB
- File naming: `escort_{timestamp}{extension}`

## 🚦 Health Check

Monitor application health:
```bash
curl http://localhost:8080/api/health
curl http://localhost:8080/api/db-test
```

## 🧪 Testing

Sample data is automatically inserted during migrations for testing purposes. The application includes 8 sample escort records with various statuses and categories.

## 🔄 Migration Benefits

As outlined in the migration guide, this Go implementation provides:

1. **Better Performance**: Compiled binary with efficient request handling
2. **Lower Resource Usage**: Reduced memory footprint compared to PHP
3. **Improved Concurrency**: Better handling of concurrent requests
4. **Simplified Deployment**: Single binary deployment
5. **Laravel Compatibility**: Maintains API contract with existing Laravel frontend

## 🎯 Next Steps

### Phase 2 (Future Development)
- Authentication with JWT tokens
- Rate limiting with Redis
- File upload optimization
- Comprehensive logging with structured logs
- API versioning
- OpenAPI/Swagger documentation

### Phase 3 (Advanced Features)
- Caching layer implementation
- Background job processing
- Metrics and monitoring
- Load balancing support

## 📖 References

This implementation follows the **Laravel to Golang API Migration Guide** specifications, specifically implementing the **HIGH PRIORITY** and **MEDIUM PRIORITY** endpoints identified for migration.