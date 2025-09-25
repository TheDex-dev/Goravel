package handlers

import (
	"net/http"

	"goserver/models"

	"github.com/gin-gonic/gin"
	"github.com/go-playground/validator/v10"
	"github.com/skip2/go-qrcode"
)

type QRCodeHandler struct {
	validator *validator.Validate
}

func NewQRCodeHandler() *QRCodeHandler {
	return &QRCodeHandler{
		validator: validator.New(),
	}
}

// GenerateQRCode handles GET /api/qr-code/form
func (h *QRCodeHandler) GenerateQRCode(c *gin.Context) {
	var req models.QRCodeRequest

	if err := c.ShouldBindQuery(&req); err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{
			Status:  "error",
			Message: "Invalid request parameters",
			Errors:  err.Error(),
		})
		return
	}

	// Set default size if not provided
	if req.Size == 0 {
		req.Size = 256
	}

	if err := h.validator.Struct(&req); err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{
			Status:  "error",
			Message: "Validation failed",
			Errors:  h.formatValidationErrors(err),
		})
		return
	}

	// Generate QR code
	png, err := qrcode.Encode(req.URL, qrcode.Medium, req.Size)
	if err != nil {
		c.JSON(http.StatusInternalServerError, models.APIResponse{
			Status:  "error",
			Message: "Failed to generate QR code",
			Errors:  err.Error(),
		})
		return
	}

	// Return as PNG image
	c.Header("Content-Type", "image/png")
	c.Header("Cache-Control", "public, max-age=3600") // Cache for 1 hour
	c.Data(http.StatusOK, "image/png", png)
}

// GenerateQRCodeJSON handles POST /api/qr-code/form (returns base64)
func (h *QRCodeHandler) GenerateQRCodeJSON(c *gin.Context) {
	var req models.QRCodeRequest

	if err := c.ShouldBindJSON(&req); err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{
			Status:  "error",
			Message: "Invalid request format",
			Errors:  err.Error(),
		})
		return
	}

	// Set default size if not provided
	if req.Size == 0 {
		req.Size = 256
	}

	if err := h.validator.Struct(&req); err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{
			Status:  "error",
			Message: "Validation failed",
			Errors:  h.formatValidationErrors(err),
		})
		return
	}

	// Generate QR code
	png, err := qrcode.Encode(req.URL, qrcode.Medium, req.Size)
	if err != nil {
		c.JSON(http.StatusInternalServerError, models.APIResponse{
			Status:  "error",
			Message: "Failed to generate QR code",
			Errors:  err.Error(),
		})
		return
	}

	// Encode as base64
	base64Data := "data:image/png;base64," + string(png)

	c.JSON(http.StatusOK, models.APIResponse{
		Status:  "success",
		Message: "QR code generated successfully",
		Data: gin.H{
			"qr_code": base64Data,
			"url":     req.URL,
			"size":    req.Size,
			"format":  "PNG",
		},
	})
}

// formatValidationErrors formats validation errors for API response
func (h *QRCodeHandler) formatValidationErrors(err error) map[string]string {
	errors := make(map[string]string)

	if validationErrors, ok := err.(validator.ValidationErrors); ok {
		for _, fieldError := range validationErrors {
			field := fieldError.Field()
			tag := fieldError.Tag()

			switch tag {
			case "required":
				errors[field] = field + " is required"
			case "min":
				errors[field] = field + " must be at least " + fieldError.Param() + " characters"
			case "max":
				errors[field] = field + " must not exceed " + fieldError.Param() + " characters"
			case "url":
				errors[field] = field + " must be a valid URL"
			default:
				errors[field] = field + " is invalid"
			}
		}
	}

	return errors
}
