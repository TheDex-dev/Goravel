package handlers

import (
	"net/http"
	"strconv"
	"strings"

	"goserver/models"
	"goserver/services"

	"github.com/gin-gonic/gin"
	"github.com/go-playground/validator/v10"
)

type EscortHandler struct {
	service   *services.EscortService
	validator *validator.Validate
}

func NewEscortHandler(service *services.EscortService) *EscortHandler {
	return &EscortHandler{
		service:   service,
		validator: validator.New(),
	}
}

// CreateEscort handles POST /api/escort
func (h *EscortHandler) CreateEscort(c *gin.Context) {
	var req models.CreateEscortRequest

	if err := c.ShouldBindJSON(&req); err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{
			Status:  "error",
			Message: "Invalid request format",
			Errors:  err.Error(),
		})
		return
	}

	if err := h.validator.Struct(&req); err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{
			Status:  "error",
			Message: "Validation failed",
			Errors:  h.formatValidationErrors(err),
		})
		return
	}

	clientIP := c.ClientIP()
	escort, err := h.service.CreateEscort(c.Request.Context(), req, clientIP)
	if err != nil {
		c.JSON(http.StatusInternalServerError, models.APIResponse{
			Status:  "error",
			Message: "Failed to create escort",
			Errors:  err.Error(),
		})
		return
	}

	c.JSON(http.StatusCreated, models.APIResponse{
		Status:  "success",
		Message: "Escort created successfully",
		Data:    escort,
	})
}

// GetEscorts handles GET /api/escort
func (h *EscortHandler) GetEscorts(c *gin.Context) {
	var filters models.EscortFilters

	if err := c.ShouldBindQuery(&filters); err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{
			Status:  "error",
			Message: "Invalid query parameters",
			Errors:  err.Error(),
		})
		return
	}

	escorts, meta, err := h.service.GetEscorts(c.Request.Context(), filters)
	if err != nil {
		c.JSON(http.StatusInternalServerError, models.APIResponse{
			Status:  "error",
			Message: "Failed to retrieve escorts",
			Errors:  err.Error(),
		})
		return
	}

	c.JSON(http.StatusOK, models.APIResponse{
		Status:  "success",
		Message: "Escorts retrieved successfully",
		Data:    escorts,
		Meta:    meta,
	})
}

// GetEscort handles GET /api/escort/:id
func (h *EscortHandler) GetEscort(c *gin.Context) {
	id, err := h.parseIDParam(c)
	if err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{
			Status:  "error",
			Message: "Invalid escort ID",
			Errors:  err.Error(),
		})
		return
	}

	escort, err := h.service.GetEscortByID(c.Request.Context(), id)
	if err != nil {
		if strings.Contains(err.Error(), "no rows") {
			c.JSON(http.StatusNotFound, models.APIResponse{
				Status:  "error",
				Message: "Escort not found",
			})
			return
		}
		c.JSON(http.StatusInternalServerError, models.APIResponse{
			Status:  "error",
			Message: "Failed to retrieve escort",
			Errors:  err.Error(),
		})
		return
	}

	c.JSON(http.StatusOK, models.APIResponse{
		Status:  "success",
		Message: "Escort retrieved successfully",
		Data:    escort,
	})
}

// UpdateEscort handles PUT/PATCH /api/escort/:id
func (h *EscortHandler) UpdateEscort(c *gin.Context) {
	id, err := h.parseIDParam(c)
	if err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{
			Status:  "error",
			Message: "Invalid escort ID",
			Errors:  err.Error(),
		})
		return
	}

	var req models.UpdateEscortRequest

	if err := c.ShouldBindJSON(&req); err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{
			Status:  "error",
			Message: "Invalid request format",
			Errors:  err.Error(),
		})
		return
	}

	if err := h.validator.Struct(&req); err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{
			Status:  "error",
			Message: "Validation failed",
			Errors:  h.formatValidationErrors(err),
		})
		return
	}

	escort, err := h.service.UpdateEscort(c.Request.Context(), id, req)
	if err != nil {
		if strings.Contains(err.Error(), "no rows") {
			c.JSON(http.StatusNotFound, models.APIResponse{
				Status:  "error",
				Message: "Escort not found",
			})
			return
		}
		c.JSON(http.StatusInternalServerError, models.APIResponse{
			Status:  "error",
			Message: "Failed to update escort",
			Errors:  err.Error(),
		})
		return
	}

	c.JSON(http.StatusOK, models.APIResponse{
		Status:  "success",
		Message: "Escort updated successfully",
		Data:    escort,
	})
}

// UpdateEscortStatus handles PATCH /api/escort/:id/status
func (h *EscortHandler) UpdateEscortStatus(c *gin.Context) {
	id, err := h.parseIDParam(c)
	if err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{
			Status:  "error",
			Message: "Invalid escort ID",
			Errors:  err.Error(),
		})
		return
	}

	var req models.UpdateStatusRequest

	if err := c.ShouldBindJSON(&req); err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{
			Status:  "error",
			Message: "Invalid request format",
			Errors:  err.Error(),
		})
		return
	}

	if err := h.validator.Struct(&req); err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{
			Status:  "error",
			Message: "Validation failed",
			Errors:  h.formatValidationErrors(err),
		})
		return
	}

	escort, err := h.service.UpdateEscortStatus(c.Request.Context(), id, req.Status)
	if err != nil {
		if strings.Contains(err.Error(), "no rows") {
			c.JSON(http.StatusNotFound, models.APIResponse{
				Status:  "error",
				Message: "Escort not found",
			})
			return
		}
		c.JSON(http.StatusInternalServerError, models.APIResponse{
			Status:  "error",
			Message: "Failed to update escort status",
			Errors:  err.Error(),
		})
		return
	}

	c.JSON(http.StatusOK, models.APIResponse{
		Status:  "success",
		Message: "Escort status updated successfully",
		Data:    escort,
	})
}

// DeleteEscort handles DELETE /api/escort/:id
func (h *EscortHandler) DeleteEscort(c *gin.Context) {
	id, err := h.parseIDParam(c)
	if err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{
			Status:  "error",
			Message: "Invalid escort ID",
			Errors:  err.Error(),
		})
		return
	}

	err = h.service.DeleteEscort(c.Request.Context(), id)
	if err != nil {
		if strings.Contains(err.Error(), "not found") {
			c.JSON(http.StatusNotFound, models.APIResponse{
				Status:  "error",
				Message: "Escort not found",
			})
			return
		}
		c.JSON(http.StatusInternalServerError, models.APIResponse{
			Status:  "error",
			Message: "Failed to delete escort",
			Errors:  err.Error(),
		})
		return
	}

	c.JSON(http.StatusOK, models.APIResponse{
		Status:  "success",
		Message: "Escort deleted successfully",
	})
}

// GetDashboardStats handles GET /api/dashboard/stats
func (h *EscortHandler) GetDashboardStats(c *gin.Context) {
	stats, err := h.service.GetDashboardStats(c.Request.Context())
	if err != nil {
		c.JSON(http.StatusInternalServerError, models.APIResponse{
			Status:  "error",
			Message: "Failed to retrieve dashboard statistics",
			Errors:  err.Error(),
		})
		return
	}

	c.JSON(http.StatusOK, models.APIResponse{
		Status:  "success",
		Message: "Dashboard statistics retrieved successfully",
		Data:    stats,
	})
}

// GetImageBase64 handles GET /api/escort/:id/image/base64
func (h *EscortHandler) GetImageBase64(c *gin.Context) {
	id, err := h.parseIDParam(c)
	if err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{
			Status:  "error",
			Message: "Invalid escort ID",
			Errors:  err.Error(),
		})
		return
	}

	base64Data, err := h.service.GetImageAsBase64(c.Request.Context(), id)
	if err != nil {
		if strings.Contains(err.Error(), "not found") || strings.Contains(err.Error(), "no image") {
			c.JSON(http.StatusNotFound, models.APIResponse{
				Status:  "error",
				Message: "Image not found",
			})
			return
		}
		c.JSON(http.StatusInternalServerError, models.APIResponse{
			Status:  "error",
			Message: "Failed to retrieve image",
			Errors:  err.Error(),
		})
		return
	}

	c.JSON(http.StatusOK, models.APIResponse{
		Status:  "success",
		Message: "Image retrieved successfully",
		Data: gin.H{
			"image_base64": base64Data,
		},
	})
}

// UploadImageBase64 handles POST /api/escort/:id/image/base64
func (h *EscortHandler) UploadImageBase64(c *gin.Context) {
	id, err := h.parseIDParam(c)
	if err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{
			Status:  "error",
			Message: "Invalid escort ID",
			Errors:  err.Error(),
		})
		return
	}

	var req struct {
		ImageBase64 string `json:"image_base64" validate:"required"`
	}

	if err := c.ShouldBindJSON(&req); err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{
			Status:  "error",
			Message: "Invalid request format",
			Errors:  err.Error(),
		})
		return
	}

	if err := h.validator.Struct(&req); err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{
			Status:  "error",
			Message: "Validation failed",
			Errors:  h.formatValidationErrors(err),
		})
		return
	}

	updateReq := models.UpdateEscortRequest{
		FotoPengantarB64: &req.ImageBase64,
	}

	escort, err := h.service.UpdateEscort(c.Request.Context(), id, updateReq)
	if err != nil {
		if strings.Contains(err.Error(), "not found") {
			c.JSON(http.StatusNotFound, models.APIResponse{
				Status:  "error",
				Message: "Escort not found",
			})
			return
		}
		c.JSON(http.StatusInternalServerError, models.APIResponse{
			Status:  "error",
			Message: "Failed to upload image",
			Errors:  err.Error(),
		})
		return
	}

	c.JSON(http.StatusOK, models.APIResponse{
		Status:  "success",
		Message: "Image uploaded successfully",
		Data:    escort,
	})
}

// parseIDParam parses the ID parameter from URL
func (h *EscortHandler) parseIDParam(c *gin.Context) (uint, error) {
	idStr := c.Param("id")
	id, err := strconv.ParseUint(idStr, 10, 32)
	if err != nil {
		return 0, err
	}
	return uint(id), nil
}

// formatValidationErrors formats validation errors for API response
func (h *EscortHandler) formatValidationErrors(err error) map[string]string {
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
			case "oneof":
				errors[field] = field + " must be one of: " + fieldError.Param()
			case "email":
				errors[field] = field + " must be a valid email address"
			case "url":
				errors[field] = field + " must be a valid URL"
			default:
				errors[field] = field + " is invalid"
			}
		}
	}

	return errors
}
