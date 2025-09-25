package database

import (
	"context"
	"fmt"
	"log"

	"github.com/jackc/pgx/v5/pgxpool"
)

// DatabaseConfig holds database connection configuration
type DatabaseConfig struct {
	Host     string
	Port     string
	Database string
	Username string
	Password string
}

// NewConnection creates a new PostgreSQL connection pool
func NewConnection(config DatabaseConfig) (*pgxpool.Pool, error) {
	// Build connection string
	connStr := fmt.Sprintf("postgres://%s:%s@%s:%s/%s?sslmode=disable",
		config.Username,
		config.Password,
		config.Host,
		config.Port,
		config.Database,
	)

	// Create connection pool with configuration
	dbConfig, err := pgxpool.ParseConfig(connStr)
	if err != nil {
		return nil, fmt.Errorf("failed to parse database config: %w", err)
	}

	// Configure connection pool
	dbConfig.MaxConns = 30
	dbConfig.MinConns = 5

	// Create connection pool
	dbpool, err := pgxpool.NewWithConfig(context.Background(), dbConfig)
	if err != nil {
		return nil, fmt.Errorf("unable to create connection pool: %w", err)
	}

	// Test the connection
	err = dbpool.Ping(context.Background())
	if err != nil {
		return nil, fmt.Errorf("unable to ping database: %w", err)
	}

	log.Println("Successfully connected to PostgreSQL database")
	return dbpool, nil
}

// RunMigrations executes database migrations
func RunMigrations(db *pgxpool.Pool) error {
	// This is a simple migration runner
	// In production, consider using a proper migration tool like golang-migrate

	migrations := []string{
		// Users table migration
		`CREATE TABLE IF NOT EXISTS users (
			id SERIAL PRIMARY KEY,
			name VARCHAR(255) NOT NULL,
			email VARCHAR(255) UNIQUE NOT NULL,
			email_verified_at TIMESTAMP NULL,
			password VARCHAR(255) NOT NULL,
			remember_token VARCHAR(100) NULL,
			created_at TIMESTAMP NOT NULL DEFAULT NOW(),
			updated_at TIMESTAMP NOT NULL DEFAULT NOW()
		)`,
		`CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)`,

		// Escorts table migration (for Pendataan IGD)
		`CREATE TABLE IF NOT EXISTS escorts (
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
		)`,

		// Escorts table indexes
		`CREATE INDEX IF NOT EXISTS idx_escorts_status ON escorts(status)`,
		`CREATE INDEX IF NOT EXISTS idx_escorts_kategori ON escorts(kategori_pengantar)`,
		`CREATE INDEX IF NOT EXISTS idx_escorts_created_at ON escorts(created_at)`,
		`CREATE INDEX IF NOT EXISTS idx_escorts_submission_id ON escorts(submission_id)`,
	}

	for i, migration := range migrations {
		_, err := db.Exec(context.Background(), migration)
		if err != nil {
			return fmt.Errorf("failed to run migration %d: %w", i+1, err)
		}
	}

	log.Println("Database migrations completed successfully")
	return nil
}
