package services

import (
	"context"
	"encoding/base64"
	"fmt"
	"io"
	"math"
	"mime"
	"os"
	"path/filepath"
	"strings"
	"time"

	"goserver/models"

	"github.com/jackc/pgx/v5/pgxpool"
)

type EscortService struct {
	db *pgxpool.Pool
}

func NewEscortService(db *pgxpool.Pool) *EscortService {
	return &EscortService{db: db}
}

// CreateEscort creates a new escort record
func (s *EscortService) CreateEscort(ctx context.Context, req models.CreateEscortRequest, clientIP string) (*models.Escort, error) {
	escort := &models.Escort{
		Status:            "pending",
		KategoriPengantar: req.KategoriPengantar,
		NamaPengantar:     req.NamaPengantar,
		JenisKelamin:      req.JenisKelamin,
		NomorHP:           req.NomorHP,
		PlatNomor:         req.PlatNomor,
		NamaPasien:        req.NamaPasien,
		SubmittedFromIP:   &clientIP,
		APISubmission:     true,
	}

	// Set custom status if provided
	if req.Status != "" {
		escort.Status = req.Status
	}

	// Handle base64 image upload
	if req.FotoPengantarB64 != "" {
		filename, err := s.saveBase64Image(req.FotoPengantarB64)
		if err != nil {
			return nil, fmt.Errorf("failed to save image: %w", err)
		}
		escort.FotoPengantar = &filename
	}

	// Generate submission ID
	submissionID := fmt.Sprintf("ESC_%d_%s", time.Now().Unix(), strings.ToUpper(escort.PlatNomor))
	escort.SubmissionID = &submissionID

	// Insert into database
	query := `
		INSERT INTO escorts (
			status, kategori_pengantar, nama_pengantar, jenis_kelamin,
			nomor_hp, plat_nomor, nama_pasien, foto_pengantar,
			submission_id, submitted_from_ip, api_submission,
			created_at, updated_at
		) VALUES (
			$1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, NOW(), NOW()
		) RETURNING id, created_at, updated_at
	`

	err := s.db.QueryRow(ctx, query,
		escort.Status, escort.KategoriPengantar, escort.NamaPengantar,
		escort.JenisKelamin, escort.NomorHP, escort.PlatNomor,
		escort.NamaPasien, escort.FotoPengantar, escort.SubmissionID,
		escort.SubmittedFromIP, escort.APISubmission,
	).Scan(&escort.ID, &escort.CreatedAt, &escort.UpdatedAt)

	if err != nil {
		return nil, fmt.Errorf("failed to create escort: %w", err)
	}

	return escort, nil
}

// GetEscorts retrieves escorts with pagination and filtering
func (s *EscortService) GetEscorts(ctx context.Context, filters models.EscortFilters) ([]models.Escort, *models.Meta, error) {
	// Set default pagination
	if filters.Page <= 0 {
		filters.Page = 1
	}
	if filters.PerPage <= 0 {
		filters.PerPage = 10
	}
	if filters.PerPage > 100 {
		filters.PerPage = 100
	}

	// Build WHERE clause
	whereClause := "WHERE 1=1"
	args := []interface{}{}
	argCount := 0

	if filters.Status != "" {
		argCount++
		whereClause += fmt.Sprintf(" AND status = $%d", argCount)
		args = append(args, filters.Status)
	}

	if filters.KategoriPengantar != "" {
		argCount++
		whereClause += fmt.Sprintf(" AND kategori_pengantar = $%d", argCount)
		args = append(args, filters.KategoriPengantar)
	}

	if filters.JenisKelamin != "" {
		argCount++
		whereClause += fmt.Sprintf(" AND jenis_kelamin = $%d", argCount)
		args = append(args, filters.JenisKelamin)
	}

	if filters.Search != "" {
		argCount++
		whereClause += fmt.Sprintf(" AND (nama_pengantar ILIKE $%d OR nama_pasien ILIKE $%d OR plat_nomor ILIKE $%d)", argCount, argCount, argCount)
		args = append(args, "%"+filters.Search+"%")
	}

	// Build ORDER BY clause
	orderClause := "ORDER BY created_at DESC"
	if filters.SortBy != "" {
		validSortFields := map[string]bool{
			"id": true, "status": true, "kategori_pengantar": true,
			"nama_pengantar": true, "nama_pasien": true, "created_at": true,
		}
		if validSortFields[filters.SortBy] {
			sortOrder := "DESC"
			if filters.SortOrder == "asc" {
				sortOrder = "ASC"
			}
			orderClause = fmt.Sprintf("ORDER BY %s %s", filters.SortBy, sortOrder)
		}
	}

	// Get total count
	countQuery := fmt.Sprintf("SELECT COUNT(*) FROM escorts %s", whereClause)
	var total int64
	err := s.db.QueryRow(ctx, countQuery, args...).Scan(&total)
	if err != nil {
		return nil, nil, fmt.Errorf("failed to get total count: %w", err)
	}

	// Calculate pagination
	offset := (filters.Page - 1) * filters.PerPage
	totalPages := int(math.Ceil(float64(total) / float64(filters.PerPage)))

	// Get escorts
	query := fmt.Sprintf(`
		SELECT id, status, kategori_pengantar, nama_pengantar, jenis_kelamin,
		       nomor_hp, plat_nomor, nama_pasien, foto_pengantar,
		       submission_id, submitted_from_ip, api_submission,
		       created_at, updated_at
		FROM escorts %s %s
		LIMIT $%d OFFSET $%d
	`, whereClause, orderClause, argCount+1, argCount+2)

	args = append(args, filters.PerPage, offset)

	rows, err := s.db.Query(ctx, query, args...)
	if err != nil {
		return nil, nil, fmt.Errorf("failed to query escorts: %w", err)
	}
	defer rows.Close()

	var escorts []models.Escort
	for rows.Next() {
		var escort models.Escort
		err := rows.Scan(
			&escort.ID, &escort.Status, &escort.KategoriPengantar,
			&escort.NamaPengantar, &escort.JenisKelamin, &escort.NomorHP,
			&escort.PlatNomor, &escort.NamaPasien, &escort.FotoPengantar,
			&escort.SubmissionID, &escort.SubmittedFromIP, &escort.APISubmission,
			&escort.CreatedAt, &escort.UpdatedAt,
		)
		if err != nil {
			return nil, nil, fmt.Errorf("failed to scan escort: %w", err)
		}
		escorts = append(escorts, escort)
	}

	meta := &models.Meta{
		CurrentPage: filters.Page,
		TotalPages:  totalPages,
		PerPage:     filters.PerPage,
		Total:       total,
	}

	return escorts, meta, nil
}

// GetEscortByID retrieves a single escort by ID
func (s *EscortService) GetEscortByID(ctx context.Context, id uint) (*models.Escort, error) {
	var escort models.Escort

	query := `
		SELECT id, status, kategori_pengantar, nama_pengantar, jenis_kelamin,
		       nomor_hp, plat_nomor, nama_pasien, foto_pengantar,
		       submission_id, submitted_from_ip, api_submission,
		       created_at, updated_at
		FROM escorts WHERE id = $1
	`

	err := s.db.QueryRow(ctx, query, id).Scan(
		&escort.ID, &escort.Status, &escort.KategoriPengantar,
		&escort.NamaPengantar, &escort.JenisKelamin, &escort.NomorHP,
		&escort.PlatNomor, &escort.NamaPasien, &escort.FotoPengantar,
		&escort.SubmissionID, &escort.SubmittedFromIP, &escort.APISubmission,
		&escort.CreatedAt, &escort.UpdatedAt,
	)

	if err != nil {
		return nil, fmt.Errorf("failed to get escort: %w", err)
	}

	return &escort, nil
}

// UpdateEscort updates an existing escort record
func (s *EscortService) UpdateEscort(ctx context.Context, id uint, req models.UpdateEscortRequest) (*models.Escort, error) {
	// Build dynamic update query
	setParts := []string{"updated_at = NOW()"}
	args := []interface{}{}
	argCount := 0

	if req.KategoriPengantar != nil {
		argCount++
		setParts = append(setParts, fmt.Sprintf("kategori_pengantar = $%d", argCount))
		args = append(args, *req.KategoriPengantar)
	}

	if req.NamaPengantar != nil {
		argCount++
		setParts = append(setParts, fmt.Sprintf("nama_pengantar = $%d", argCount))
		args = append(args, *req.NamaPengantar)
	}

	if req.JenisKelamin != nil {
		argCount++
		setParts = append(setParts, fmt.Sprintf("jenis_kelamin = $%d", argCount))
		args = append(args, *req.JenisKelamin)
	}

	if req.NomorHP != nil {
		argCount++
		setParts = append(setParts, fmt.Sprintf("nomor_hp = $%d", argCount))
		args = append(args, *req.NomorHP)
	}

	if req.PlatNomor != nil {
		argCount++
		setParts = append(setParts, fmt.Sprintf("plat_nomor = $%d", argCount))
		args = append(args, *req.PlatNomor)
	}

	if req.NamaPasien != nil {
		argCount++
		setParts = append(setParts, fmt.Sprintf("nama_pasien = $%d", argCount))
		args = append(args, *req.NamaPasien)
	}

	// Handle image update
	if req.FotoPengantarB64 != nil && *req.FotoPengantarB64 != "" {
		filename, err := s.saveBase64Image(*req.FotoPengantarB64)
		if err != nil {
			return nil, fmt.Errorf("failed to save image: %w", err)
		}
		argCount++
		setParts = append(setParts, fmt.Sprintf("foto_pengantar = $%d", argCount))
		args = append(args, filename)
	}

	if len(setParts) == 1 { // Only updated_at
		return s.GetEscortByID(ctx, id)
	}

	argCount++
	query := fmt.Sprintf("UPDATE escorts SET %s WHERE id = $%d", strings.Join(setParts, ", "), argCount)
	args = append(args, id)

	_, err := s.db.Exec(ctx, query, args...)
	if err != nil {
		return nil, fmt.Errorf("failed to update escort: %w", err)
	}

	return s.GetEscortByID(ctx, id)
}

// UpdateEscortStatus updates the status of an escort
func (s *EscortService) UpdateEscortStatus(ctx context.Context, id uint, status string) (*models.Escort, error) {
	query := "UPDATE escorts SET status = $1, updated_at = NOW() WHERE id = $2"
	_, err := s.db.Exec(ctx, query, status, id)
	if err != nil {
		return nil, fmt.Errorf("failed to update escort status: %w", err)
	}

	return s.GetEscortByID(ctx, id)
}

// DeleteEscort deletes an escort record
func (s *EscortService) DeleteEscort(ctx context.Context, id uint) error {
	// First get the escort to check if it has an image
	escort, err := s.GetEscortByID(ctx, id)
	if err != nil {
		return fmt.Errorf("failed to get escort for deletion: %w", err)
	}

	// Delete image file if exists
	if escort.FotoPengantar != nil && *escort.FotoPengantar != "" {
		s.deleteImageFile(*escort.FotoPengantar)
	}

	query := "DELETE FROM escorts WHERE id = $1"
	result, err := s.db.Exec(ctx, query, id)
	if err != nil {
		return fmt.Errorf("failed to delete escort: %w", err)
	}

	rowsAffected := result.RowsAffected()
	if rowsAffected == 0 {
		return fmt.Errorf("escort not found")
	}

	return nil
}

// GetDashboardStats retrieves dashboard statistics
func (s *EscortService) GetDashboardStats(ctx context.Context) (*models.DashboardStats, error) {
	stats := &models.DashboardStats{
		CategoryStats:   make(map[string]int64),
		StatusBreakdown: make(map[string]int64),
	}

	// Get total counts
	totalQuery := "SELECT COUNT(*) FROM escorts"
	err := s.db.QueryRow(ctx, totalQuery).Scan(&stats.TotalEscorts)
	if err != nil {
		return nil, fmt.Errorf("failed to get total escorts: %w", err)
	}

	// Get status counts
	statusQuery := `
		SELECT 
			COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
			COUNT(CASE WHEN status = 'verified' THEN 1 END) as verified,
			COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected
		FROM escorts
	`
	err = s.db.QueryRow(ctx, statusQuery).Scan(
		&stats.PendingEscorts,
		&stats.VerifiedEscorts,
		&stats.RejectedEscorts,
	)
	if err != nil {
		return nil, fmt.Errorf("failed to get status counts: %w", err)
	}

	// Get today's submissions
	todayQuery := "SELECT COUNT(*) FROM escorts WHERE DATE(created_at) = CURRENT_DATE"
	err = s.db.QueryRow(ctx, todayQuery).Scan(&stats.TodaySubmissions)
	if err != nil {
		return nil, fmt.Errorf("failed to get today's submissions: %w", err)
	}

	// Get category breakdown
	categoryQuery := "SELECT kategori_pengantar, COUNT(*) FROM escorts GROUP BY kategori_pengantar"
	rows, err := s.db.Query(ctx, categoryQuery)
	if err != nil {
		return nil, fmt.Errorf("failed to get category stats: %w", err)
	}
	defer rows.Close()

	for rows.Next() {
		var category string
		var count int64
		err := rows.Scan(&category, &count)
		if err != nil {
			return nil, fmt.Errorf("failed to scan category stats: %w", err)
		}
		stats.CategoryStats[category] = count
	}

	// Get status breakdown
	statusBreakdownQuery := "SELECT status, COUNT(*) FROM escorts GROUP BY status"
	rows, err = s.db.Query(ctx, statusBreakdownQuery)
	if err != nil {
		return nil, fmt.Errorf("failed to get status breakdown: %w", err)
	}
	defer rows.Close()

	for rows.Next() {
		var status string
		var count int64
		err := rows.Scan(&status, &count)
		if err != nil {
			return nil, fmt.Errorf("failed to scan status breakdown: %w", err)
		}
		stats.StatusBreakdown[status] = count
	}

	// Get recent escorts (last 5)
	recentQuery := `
		SELECT id, status, kategori_pengantar, nama_pengantar, jenis_kelamin,
		       nomor_hp, plat_nomor, nama_pasien, foto_pengantar,
		       submission_id, submitted_from_ip, api_submission,
		       created_at, updated_at
		FROM escorts 
		ORDER BY created_at DESC 
		LIMIT 5
	`
	rows, err = s.db.Query(ctx, recentQuery)
	if err != nil {
		return nil, fmt.Errorf("failed to get recent escorts: %w", err)
	}
	defer rows.Close()

	for rows.Next() {
		var escort models.Escort
		err := rows.Scan(
			&escort.ID, &escort.Status, &escort.KategoriPengantar,
			&escort.NamaPengantar, &escort.JenisKelamin, &escort.NomorHP,
			&escort.PlatNomor, &escort.NamaPasien, &escort.FotoPengantar,
			&escort.SubmissionID, &escort.SubmittedFromIP, &escort.APISubmission,
			&escort.CreatedAt, &escort.UpdatedAt,
		)
		if err != nil {
			return nil, fmt.Errorf("failed to scan recent escort: %w", err)
		}
		stats.RecentEscorts = append(stats.RecentEscorts, escort)
	}

	return stats, nil
}

// GetImageAsBase64 returns an image as base64 string
func (s *EscortService) GetImageAsBase64(ctx context.Context, id uint) (string, error) {
	escort, err := s.GetEscortByID(ctx, id)
	if err != nil {
		return "", err
	}

	if escort.FotoPengantar == nil || *escort.FotoPengantar == "" {
		return "", fmt.Errorf("no image found for escort")
	}

	return s.loadImageAsBase64(*escort.FotoPengantar)
}

// saveBase64Image saves a base64 encoded image to file system
func (s *EscortService) saveBase64Image(base64Data string) (string, error) {
	// Parse data URL (data:image/jpeg;base64,...)
	parts := strings.SplitN(base64Data, ",", 2)
	if len(parts) != 2 {
		return "", fmt.Errorf("invalid base64 data format")
	}

	// Extract MIME type
	mimeType := "image/jpeg" // default
	if strings.HasPrefix(parts[0], "data:") {
		mimeType = strings.TrimPrefix(strings.Split(parts[0], ";")[0], "data:")
	}

	// Validate MIME type
	validTypes := map[string]string{
		"image/jpeg": ".jpg",
		"image/jpg":  ".jpg",
		"image/png":  ".png",
		"image/gif":  ".gif",
	}

	ext, valid := validTypes[mimeType]
	if !valid {
		return "", fmt.Errorf("unsupported image format: %s", mimeType)
	}

	// Decode base64
	data, err := base64.StdEncoding.DecodeString(parts[1])
	if err != nil {
		return "", fmt.Errorf("failed to decode base64: %w", err)
	}

	// Check file size (2MB limit)
	if len(data) > 2*1024*1024 {
		return "", fmt.Errorf("image too large (max 2MB)")
	}

	// Create uploads directory if not exists
	uploadDir := "storage/uploads"
	err = os.MkdirAll(uploadDir, 0755)
	if err != nil {
		return "", fmt.Errorf("failed to create upload directory: %w", err)
	}

	// Generate unique filename
	filename := fmt.Sprintf("escort_%d%s", time.Now().UnixNano(), ext)
	filepath := filepath.Join(uploadDir, filename)

	// Save file
	file, err := os.Create(filepath)
	if err != nil {
		return "", fmt.Errorf("failed to create file: %w", err)
	}
	defer file.Close()

	_, err = file.Write(data)
	if err != nil {
		return "", fmt.Errorf("failed to write file: %w", err)
	}

	return filename, nil
}

// loadImageAsBase64 loads an image file and returns it as base64
func (s *EscortService) loadImageAsBase64(filename string) (string, error) {
	filepath := filepath.Join("storage/uploads", filename)

	file, err := os.Open(filepath)
	if err != nil {
		return "", fmt.Errorf("failed to open image file: %w", err)
	}
	defer file.Close()

	// Read file
	data, err := io.ReadAll(file)
	if err != nil {
		return "", fmt.Errorf("failed to read image file: %w", err)
	}

	// Detect MIME type based on file extension
	lastDot := strings.LastIndex(filename, ".")
	ext := ""
	if lastDot >= 0 {
		ext = filename[lastDot:]
	}
	mimeType := mime.TypeByExtension(ext)
	if mimeType == "" {
		mimeType = "image/jpeg" // default
	}

	// Encode as base64 data URL
	encoded := base64.StdEncoding.EncodeToString(data)
	return fmt.Sprintf("data:%s;base64,%s", mimeType, encoded), nil
}

// deleteImageFile deletes an image file from file system
func (s *EscortService) deleteImageFile(filename string) {
	filepath := filepath.Join("storage/uploads", filename)
	os.Remove(filepath) // Ignore errors for cleanup
}
