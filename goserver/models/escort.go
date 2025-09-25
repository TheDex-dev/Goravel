package models

import (
	"time"
)

// Escort represents the main entity for the Pendataan IGD system
type Escort struct {
	ID                uint      `json:"id" db:"id"`
	Status            string    `json:"status" db:"status"`
	KategoriPengantar string    `json:"kategori_pengantar" db:"kategori_pengantar"`
	NamaPengantar     string    `json:"nama_pengantar" db:"nama_pengantar"`
	JenisKelamin      string    `json:"jenis_kelamin" db:"jenis_kelamin"`
	NomorHP           string    `json:"nomor_hp" db:"nomor_hp"`
	PlatNomor         string    `json:"plat_nomor" db:"plat_nomor"`
	NamaPasien        string    `json:"nama_pasien" db:"nama_pasien"`
	FotoPengantar     *string   `json:"foto_pengantar" db:"foto_pengantar"`
	SubmissionID      *string   `json:"submission_id" db:"submission_id"`
	SubmittedFromIP   *string   `json:"submitted_from_ip" db:"submitted_from_ip"`
	APISubmission     bool      `json:"api_submission" db:"api_submission"`
	CreatedAt         time.Time `json:"created_at" db:"created_at"`
	UpdatedAt         time.Time `json:"updated_at" db:"updated_at"`
}

// CreateEscortRequest represents the request payload for creating an escort
type CreateEscortRequest struct {
	KategoriPengantar string `json:"kategori_pengantar" validate:"required,oneof=Polisi Ambulans Perorangan"`
	NamaPengantar     string `json:"nama_pengantar" validate:"required,min=3,max=255"`
	JenisKelamin      string `json:"jenis_kelamin" validate:"required,oneof=Laki-laki Perempuan"`
	NomorHP           string `json:"nomor_hp" validate:"required,min=10,max=20"`
	PlatNomor         string `json:"plat_nomor" validate:"required,min=3,max=20"`
	NamaPasien        string `json:"nama_pasien" validate:"required,min=3,max=255"`
	FotoPengantarB64  string `json:"foto_pengantar_base64,omitempty"`
	Status            string `json:"status" validate:"omitempty,oneof=pending verified rejected"`
}

// UpdateEscortRequest represents the request payload for updating an escort
type UpdateEscortRequest struct {
	KategoriPengantar *string `json:"kategori_pengantar,omitempty" validate:"omitempty,oneof=Polisi Ambulans Perorangan"`
	NamaPengantar     *string `json:"nama_pengantar,omitempty" validate:"omitempty,min=3,max=255"`
	JenisKelamin      *string `json:"jenis_kelamin,omitempty" validate:"omitempty,oneof=Laki-laki Perempuan"`
	NomorHP           *string `json:"nomor_hp,omitempty" validate:"omitempty,min=10,max=20"`
	PlatNomor         *string `json:"plat_nomor,omitempty" validate:"omitempty,min=3,max=20"`
	NamaPasien        *string `json:"nama_pasien,omitempty" validate:"omitempty,min=3,max=255"`
	FotoPengantarB64  *string `json:"foto_pengantar_base64,omitempty"`
}

// UpdateStatusRequest represents the request payload for updating escort status
type UpdateStatusRequest struct {
	Status string `json:"status" validate:"required,oneof=pending verified rejected"`
}

// APIResponse represents the standard API response format
type APIResponse struct {
	Status  string      `json:"status"`
	Message string      `json:"message"`
	Data    interface{} `json:"data,omitempty"`
	Meta    *Meta       `json:"meta,omitempty"`
	Errors  interface{} `json:"errors,omitempty"`
}

// Meta represents pagination metadata
type Meta struct {
	CurrentPage int   `json:"current_page,omitempty"`
	TotalPages  int   `json:"total_pages,omitempty"`
	PerPage     int   `json:"per_page,omitempty"`
	Total       int64 `json:"total,omitempty"`
}

// EscortFilters represents query filters for escort listing
type EscortFilters struct {
	Status            string `form:"status"`
	KategoriPengantar string `form:"kategori_pengantar"`
	JenisKelamin      string `form:"jenis_kelamin"`
	Search            string `form:"search"`
	Page              int    `form:"page"`
	PerPage           int    `form:"per_page"`
	SortBy            string `form:"sort_by"`
	SortOrder         string `form:"sort_order"`
}

// DashboardStats represents dashboard statistics
type DashboardStats struct {
	TotalEscorts     int64            `json:"total_escorts"`
	PendingEscorts   int64            `json:"pending_escorts"`
	VerifiedEscorts  int64            `json:"verified_escorts"`
	RejectedEscorts  int64            `json:"rejected_escorts"`
	TodaySubmissions int64            `json:"today_submissions"`
	CategoryStats    map[string]int64 `json:"category_stats"`
	RecentEscorts    []Escort         `json:"recent_escorts"`
	StatusBreakdown  map[string]int64 `json:"status_breakdown"`
}

// QRCodeRequest represents QR code generation request
type QRCodeRequest struct {
	URL  string `json:"url" validate:"required,url"`
	Size int    `json:"size" validate:"omitempty,min=100,max=1000"`
}
