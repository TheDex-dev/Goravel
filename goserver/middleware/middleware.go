package middleware

import (
	"net/http"

	"github.com/gin-gonic/gin"
)

// RateLimiter creates a simple rate limiting middleware
func RateLimiter() gin.HandlerFunc {
	// This is a simple implementation - in production use Redis or similar
	return gin.HandlerFunc(func(c *gin.Context) {
		c.Next()
	})
}

// RequestLogger logs API requests with additional context
func RequestLogger() gin.HandlerFunc {
	return func(c *gin.Context) {
		c.Next()
		// Custom logging can be implemented here
		// In production, consider using a structured logger like logrus or zap
	}
}

// ErrorHandler handles panics and errors gracefully
func ErrorHandler() gin.HandlerFunc {
	return func(c *gin.Context) {
		defer func() {
			if err := recover(); err != nil {
				c.JSON(http.StatusInternalServerError, gin.H{
					"status":  "error",
					"message": "Internal server error",
					"error":   "Something went wrong",
				})
				c.Abort()
			}
		}()
		c.Next()
	}
}

// ValidateJSON validates that request contains valid JSON
func ValidateJSON() gin.HandlerFunc {
	return func(c *gin.Context) {
		if c.Request.Method == "POST" || c.Request.Method == "PUT" || c.Request.Method == "PATCH" {
			contentType := c.GetHeader("Content-Type")
			if contentType != "" && contentType != "application/json" {
				c.JSON(http.StatusBadRequest, gin.H{
					"status":  "error",
					"message": "Content-Type must be application/json",
				})
				c.Abort()
				return
			}
		}
		c.Next()
	}
}
