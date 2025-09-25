package main

import (
	"context"
	"fmt"
	"log"
	"net/http"
	"os"

	"goserver/database"
	"goserver/handlers"
	"goserver/services"

	"github.com/gin-gonic/gin"
	"github.com/jackc/pgx/v5/pgxpool"
	"github.com/joho/godotenv"
)

type Server struct {
	db     *pgxpool.Pool
	router *gin.Engine
}

type Config struct {
	DBHost     string
	DBPort     string
	DBDatabase string
	DBUsername string
	DBPassword string
	AppURL     string
	AppEnv     string
}

func loadConfig() (*Config, error) {
	// Load .env file
	err := godotenv.Load()
	if err != nil {
		return nil, fmt.Errorf("error loading .env file: %w", err)
	}

	config := &Config{
		DBHost:     getEnv("DB_HOST", "127.0.0.1"),
		DBPort:     getEnv("DB_PORT", "5432"),
		DBDatabase: getEnv("DB_DATABASE", "laravel_app"),
		DBUsername: getEnv("DB_USERNAME", "laravel_user"),
		DBPassword: getEnv("DB_PASSWORD", ""),
		AppURL:     getEnv("APP_URL", "http://localhost:8080"),
		AppEnv:     getEnv("APP_ENV", "local"),
	}

	return config, nil
}

func getEnv(key, defaultValue string) string {
	if value := os.Getenv(key); value != "" {
		return value
	}
	return defaultValue
}

func (s *Server) connectDatabase(config *Config) error {
	// Use the database package for connection
	dbConfig := database.DatabaseConfig{
		Host:     config.DBHost,
		Port:     config.DBPort,
		Database: config.DBDatabase,
		Username: config.DBUsername,
		Password: config.DBPassword,
	}

	dbpool, err := database.NewConnection(dbConfig)
	if err != nil {
		return fmt.Errorf("failed to connect to database: %w", err)
	}

	// Run migrations
	err = database.RunMigrations(dbpool)
	if err != nil {
		return fmt.Errorf("failed to run migrations: %w", err)
	}

	s.db = dbpool
	return nil
}

func (s *Server) setupRoutes() {
	// Middleware
	s.router.Use(gin.Logger())
	s.router.Use(gin.Recovery())

	// CORS middleware for Laravel frontend
	s.router.Use(func(c *gin.Context) {
		c.Header("Access-Control-Allow-Origin", "*")
		c.Header("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, PATCH, OPTIONS")
		c.Header("Access-Control-Allow-Headers", "Content-Type, Authorization")

		if c.Request.Method == "OPTIONS" {
			c.AbortWithStatus(204)
			return
		}

		c.Next()
	})

	// Initialize services and handlers
	escortService := services.NewEscortService(s.db)
	escortHandler := handlers.NewEscortHandler(escortService)
	qrHandler := handlers.NewQRCodeHandler()

	// API routes
	api := s.router.Group("/api")
	{
		// Health check endpoint
		api.GET("/health", s.healthCheck)
		api.GET("/db-test", s.dbTest)

		// HIGH PRIORITY - Core Escort API Endpoints (from migration guide)
		api.GET("/escort", escortHandler.GetEscorts)          // List escorts with filtering/pagination
		api.POST("/escort", escortHandler.CreateEscort)       // Create new escort record
		api.GET("/escort/:id", escortHandler.GetEscort)       // Get single escort record
		api.PUT("/escort/:id", escortHandler.UpdateEscort)    // Update escort record
		api.PATCH("/escort/:id", escortHandler.UpdateEscort)  // Update escort record
		api.DELETE("/escort/:id", escortHandler.DeleteEscort) // Delete escort record

		// Status Management
		api.PATCH("/escort/:id/status", escortHandler.UpdateEscortStatus) // Update escort status

		// Dashboard Statistics
		api.GET("/dashboard/stats", escortHandler.GetDashboardStats) // Get dashboard statistics
		api.GET("/session-stats", escortHandler.GetDashboardStats)   // Get session statistics (same as dashboard)

		// MEDIUM PRIORITY - Image Management Endpoints
		api.GET("/escort/:id/image/base64", escortHandler.GetImageBase64)     // Get image as base64
		api.POST("/escort/:id/image/base64", escortHandler.UploadImageBase64) // Upload image as base64

		// QR Code Generation
		api.GET("/qr-code/form", qrHandler.GenerateQRCode)      // Generate QR code for form
		api.POST("/qr-code/form", qrHandler.GenerateQRCodeJSON) // Generate QR code as JSON

		// Legacy user endpoints (for compatibility)
		v1 := api.Group("/v1")
		{
			v1.GET("/users", s.getUsers)
			v1.POST("/users", s.createUser)
			v1.GET("/users/:id", s.getUser)
			v1.PUT("/users/:id", s.updateUser)
			v1.DELETE("/users/:id", s.deleteUser)
		}
	}

	// Root endpoint
	s.router.GET("/", func(c *gin.Context) {
		c.JSON(http.StatusOK, gin.H{
			"message":     "Pendataan IGD - Go API Server",
			"status":      "running",
			"version":     "1.0.0",
			"description": "Laravel to Golang Migration - Phase 1: Core CRUD Operations",
			"endpoints": gin.H{
				"escorts":   "/api/escort",
				"dashboard": "/api/dashboard/stats",
				"qr_codes":  "/api/qr-code/form",
				"health":    "/api/health",
			},
		})
	})
}

// Health check handler
func (s *Server) healthCheck(c *gin.Context) {
	c.JSON(http.StatusOK, gin.H{
		"status":    "healthy",
		"database":  "connected",
		"timestamp": fmt.Sprintf("%v", context.Background()),
	})
}

// Database test handler
func (s *Server) dbTest(c *gin.Context) {
	var result int
	err := s.db.QueryRow(context.Background(), "SELECT 1").Scan(&result)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{
			"error":   "Database query failed",
			"details": err.Error(),
		})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"message": "Database connection successful",
		"result":  result,
	})
}

// User handlers (Laravel-compatible structure)
func (s *Server) getUsers(c *gin.Context) {
	rows, err := s.db.Query(context.Background(), "SELECT id, name, email, created_at FROM users ORDER BY id")
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	defer rows.Close()

	var users []map[string]interface{}
	for rows.Next() {
		var id int
		var name, email string
		var createdAt interface{}

		err := rows.Scan(&id, &name, &email, &createdAt)
		if err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
			return
		}

		users = append(users, map[string]interface{}{
			"id":         id,
			"name":       name,
			"email":      email,
			"created_at": createdAt,
		})
	}

	c.JSON(http.StatusOK, gin.H{
		"data": users,
	})
}

func (s *Server) createUser(c *gin.Context) {
	var user struct {
		Name     string `json:"name" binding:"required"`
		Email    string `json:"email" binding:"required,email"`
		Password string `json:"password" binding:"required,min=6"`
	}

	if err := c.ShouldBindJSON(&user); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	var userID int
	err := s.db.QueryRow(context.Background(),
		"INSERT INTO users (name, email, password, created_at, updated_at) VALUES ($1, $2, $3, NOW(), NOW()) RETURNING id",
		user.Name, user.Email, user.Password).Scan(&userID)

	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusCreated, gin.H{
		"message": "User created successfully",
		"id":      userID,
	})
}

func (s *Server) getUser(c *gin.Context) {
	id := c.Param("id")

	var user struct {
		ID        int    `json:"id"`
		Name      string `json:"name"`
		Email     string `json:"email"`
		CreatedAt string `json:"created_at"`
	}

	err := s.db.QueryRow(context.Background(),
		"SELECT id, name, email, created_at FROM users WHERE id = $1", id).
		Scan(&user.ID, &user.Name, &user.Email, &user.CreatedAt)

	if err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "User not found"})
		return
	}

	c.JSON(http.StatusOK, gin.H{"data": user})
}

func (s *Server) updateUser(c *gin.Context) {
	id := c.Param("id")

	var user struct {
		Name  string `json:"name"`
		Email string `json:"email"`
	}

	if err := c.ShouldBindJSON(&user); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	_, err := s.db.Exec(context.Background(),
		"UPDATE users SET name = $1, email = $2, updated_at = NOW() WHERE id = $3",
		user.Name, user.Email, id)

	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusOK, gin.H{"message": "User updated successfully"})
}

func (s *Server) deleteUser(c *gin.Context) {
	id := c.Param("id")

	_, err := s.db.Exec(context.Background(), "DELETE FROM users WHERE id = $1", id)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusOK, gin.H{"message": "User deleted successfully"})
}

func main() {
	// Load configuration
	config, err := loadConfig()
	if err != nil {
		log.Fatal("Failed to load configuration:", err)
	}

	// Initialize server
	server := &Server{
		router: gin.Default(),
	}

	// Connect to database
	if err := server.connectDatabase(config); err != nil {
		log.Fatal("Failed to connect to database:", err)
	}
	defer server.db.Close()

	// Setup routes
	server.setupRoutes()

	// Start server
	port := getEnv("PORT", "8080")
	log.Printf("Starting server on port %s", port)
	log.Printf("Environment: %s", config.AppEnv)
	log.Fatal(server.router.Run(":" + port))
}
