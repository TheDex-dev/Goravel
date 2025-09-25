# Go API Backend Server for Laravel

This is a Go-based API backend server built with Gin and PostgreSQL (using PGX driver) that's designed to work with a Laravel frontend application.

## Features

- **Gin Web Framework**: Fast HTTP web framework for Go
- **PostgreSQL Integration**: Using PGX v5 for efficient database operations
- **Environment Configuration**: Reads configuration from `.env` file (Laravel compatible)
- **CORS Support**: Configured for cross-origin requests from Laravel frontend
- **RESTful API**: Standard REST endpoints for user management
- **Connection Pooling**: Optimized database connection management
- **Health Checks**: Built-in health and database connectivity endpoints

## Prerequisites

- Go 1.25 or higher
- PostgreSQL database
- Laravel application with existing `.env` file

## Installation

1. Clone or navigate to the project directory:
   ```bash
   cd /home/stolas/goserver
   ```

2. Install Go dependencies:
   ```bash
   go mod tidy
   ```

3. Ensure your PostgreSQL database is running and accessible with the credentials in your `.env` file.

## Configuration

The server reads configuration from the `.env` file. Key database settings:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel_app
DB_USERNAME=laravel_user
DB_PASSWORD=
```

## Database Setup

1. Create the PostgreSQL database specified in your `.env` file:
   ```sql
   CREATE DATABASE laravel_app;
   CREATE USER laravel_user WITH PASSWORD '';
   GRANT ALL PRIVILEGES ON DATABASE laravel_app TO laravel_user;
   ```

2. The application will automatically create the necessary tables when it starts.

## Running the Server

1. Start the server:
   ```bash
   go run main.go
   ```

2. The server will start on port 8080 by default (or the PORT environment variable if set).

3. Visit `http://localhost:8080` to see the server status.

## API Endpoints

### Health & Status
- `GET /` - Server status
- `GET /api/v1/health` - Health check
- `GET /api/v1/db-test` - Database connectivity test

### User Management
- `GET /api/v1/users` - Get all users
- `POST /api/v1/users` - Create a new user
- `GET /api/v1/users/:id` - Get user by ID
- `PUT /api/v1/users/:id` - Update user
- `DELETE /api/v1/users/:id` - Delete user

### Example API Usage

#### Create a user:
```bash
curl -X POST http://localhost:8080/api/v1/users \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
  }'
```

#### Get all users:
```bash
curl http://localhost:8080/api/v1/users
```

## Project Structure

```
goserver/
├── main.go                          # Main application file
├── database/
│   ├── database.go                  # Database connection and utilities
│   └── migrations/
│       └── 001_create_users_table.sql # Database schema
├── go.mod                           # Go module dependencies
├── go.sum                           # Dependency checksums
└── .env                            # Environment configuration
```

## Laravel Integration

This Go server is designed to work alongside your Laravel application:

1. **Same Database**: Shares the PostgreSQL database with Laravel
2. **Compatible Schema**: User table structure matches Laravel's default schema
3. **CORS Enabled**: Allows requests from your Laravel frontend
4. **Environment Sync**: Uses the same `.env` configuration file

## Development

### Adding New Endpoints

1. Add route handlers in `main.go`
2. Register routes in the `setupRoutes()` function
3. Use the database connection via `s.db` for database operations

### Database Operations

The server uses PGX v5 for database operations:

```go
// Query single row
var user User
err := s.db.QueryRow(context.Background(), 
    "SELECT id, name, email FROM users WHERE id = $1", userID).
    Scan(&user.ID, &user.Name, &user.Email)

// Query multiple rows
rows, err := s.db.Query(context.Background(), 
    "SELECT id, name, email FROM users")

// Execute statement
_, err := s.db.Exec(context.Background(), 
    "INSERT INTO users (name, email) VALUES ($1, $2)", name, email)
```

## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `DB_HOST` | PostgreSQL host | 127.0.0.1 |
| `DB_PORT` | PostgreSQL port | 5432 |
| `DB_DATABASE` | Database name | laravel_app |
| `DB_USERNAME` | Database username | laravel_user |
| `DB_PASSWORD` | Database password | (empty) |
| `PORT` | Server port | 8080 |
| `APP_ENV` | Application environment | local |

## Production Considerations

1. **Security**: Implement proper authentication and authorization
2. **Logging**: Add structured logging with proper log levels  
3. **Monitoring**: Add metrics and monitoring endpoints
4. **Error Handling**: Implement comprehensive error handling
5. **Testing**: Add unit and integration tests
6. **Docker**: Consider containerizing the application
7. **Migration**: Use a proper migration tool like golang-migrate

## Troubleshooting

### Database Connection Issues
- Verify PostgreSQL is running
- Check database credentials in `.env`
- Ensure database exists and user has proper permissions

### Port Conflicts
- Change the PORT environment variable if 8080 is in use
- Check if Laravel development server is using the same port

### CORS Issues
- Verify CORS headers are properly configured for your Laravel frontend URL
- Check browser developer tools for CORS-related errors

## License

This project is part of your Laravel application setup.