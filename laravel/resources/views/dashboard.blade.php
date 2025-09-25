@extends('layout.app')

@section('title', 'Dashboard IGD')

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
    /* Reset and base styles */
    * {
        box-sizing: border-box;
    }
    
    /* Custom styles for SweetAlert2 popups */
    .swal-wide {
        width: 600px !important;
        max-width: 90% !important;
    }
    
    .swal2-popup .table {
        margin-bottom: 0;
        font-size: 0.9rem;
    }
    
    .swal2-popup .table td {
        padding: 0.4rem;
        vertical-align: middle;
    }
    
    .swal2-popup .table td:first-child {
        width: 35%;
        color: #6c757d;
    }
    
    .text-left {
        text-align: left !important;
    }
    
    .swal2-popup .alert {
        margin-bottom: 0;
        font-size: 0.9rem;
    }
    
    /* Enhanced card styling */
    .card {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }
    
    .card:hover {
        box-shadow: 0 0.25rem 2rem 0 rgba(58, 59, 69, 0.2) !important;
        transform: translateY(-2px);
    }
    
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom: none;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }
    
    .card-header h6 {
        margin: 0;
        font-weight: 600;
        font-size: 1rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    /* Statistics cards */
    .bg-gradient-primary {
        background: linear-gradient(45deg, #4e73df, #6c5ce7);
    }
    
    .bg-gradient-info {
        background: linear-gradient(45deg, #36b9cc, #00cec9);
    }
    
    .bg-gradient-warning {
        background: linear-gradient(45deg, #f6c23e, #fdcb6e);
    }
    
    .bg-gradient-success {
        background: linear-gradient(45deg, #1cc88a, #00b894);
    }
    
    /* Form controls */
    .form-control, .form-select {
        border: 1px solid #d1d3e2;
        border-radius: 0.375rem;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
    
    .form-control-lg {
        font-size: 1rem;
        padding: 0.75rem 1rem;
    }
    
    .form-label {
        color: #5a5c69;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    
    .form-label i {
        margin-right: 0.375rem;
        color: #858796;
    }
    
    /* Buttons */
    .btn {
        border-radius: 0.375rem;
        font-weight: 500;
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
        border: none;
    }
    
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }
    
    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.8rem;
    }
    
    .btn-primary {
        background: linear-gradient(45deg, #4e73df, #6c5ce7);
        box-shadow: 0 4px 15px 0 rgba(78, 115, 223, 0.3);
    }
    
    .btn-primary:hover {
        background: linear-gradient(45deg, #3d5abe, #5a4fcf);
        transform: translateY(-1px);
        box-shadow: 0 6px 20px 0 rgba(78, 115, 223, 0.4);
    }
    
    .btn-success {
        background: linear-gradient(45deg, #1cc88a, #00b894);
        box-shadow: 0 4px 15px 0 rgba(28, 200, 138, 0.3);
    }
    
    .btn-success:hover {
        background: linear-gradient(45deg, #17a673, #00a085);
        transform: translateY(-1px);
        box-shadow: 0 6px 20px 0 rgba(28, 200, 138, 0.4);
    }
    
    .btn-info {
        background: linear-gradient(45deg, #36b9cc, #00cec9);
        box-shadow: 0 4px 15px 0 rgba(54, 185, 204, 0.3);
    }
    
    .btn-info:hover {
        background: linear-gradient(45deg, #2c9faf, #00b3a8);
        transform: translateY(-1px);
        box-shadow: 0 6px 20px 0 rgba(54, 185, 204, 0.4);
    }
    
    /* Advanced filter section */
    .filter-section {
        background: #f8f9fc;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-top: 1rem;
        border: 1px solid #e3e6f0;
    }
    
    /* Filter groups */
    .filter-group {
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    
    .filter-group .form-check-input {
        margin: 0;
        width: 1.25em;
        height: 1.25em;
    }
    
    .filter-group .form-check-label {
        font-size: 0.875rem;
        cursor: pointer;
        margin: 0;
        padding-left: 0.25rem;
    }
    
    /* Date controls */
    .date-control-group {
        background: white;
        border: 1px solid #e3e6f0;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-top: 0.5rem;
    }
    
    .form-control[type="date"] {
        padding: 0.75rem;
        border-radius: 0.375rem;
        border: 1px solid #d1d3e2;
    }
    
    .form-control[type="date"]:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
    
    /* Quick date buttons */
    .quick-date-section {
        background: white;
        border: 1px solid #e3e6f0;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .quick-date {
        margin: 0.25rem;
        font-size: 0.8rem;
        padding: 0.375rem 0.75rem;
        white-space: nowrap;
    }
    
    /* Status indicator */
    .filter-status-indicator {
        background: #e8f4f8;
        border: 1px solid #bee5eb;
        border-radius: 0.375rem;
        padding: 0.75rem;
        font-size: 0.875rem;
        margin-top: 1rem;
    }
    
    /* Download section */
    .download-section {
        background: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%);
        border-radius: 0.5rem;
        padding: 1.5rem;
    }
    
    .download-preview {
        background: white;
        border: 1px solid #e3e6f0;
        border-radius: 0.375rem;
        padding: 1.5rem;
        margin: 1rem 0;
    }
    
    /* Table enhancements */
    .table-responsive {
        max-height: 600px;
        overflow-y: auto;
        border-radius: 0.375rem;
        border: 1px solid #e3e6f0;
    }
    
    .table th {
        background: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%);
        color: #5a5c69;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #dee2e6;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    /* Badge styling */
    .badge-warning {
        background-color: #f6c23e !important;
        color: #1f2937 !important;
    }
    .badge-success {
        background-color: #1cc88a !important;
        color: white !important;
    }
    .badge-danger {
        background-color: #e74a3b !important;
        color: white !important;
    }
    
    /* Status update buttons */
    .btn-status-update {
        margin: 1px;
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    .btn-group .btn {
        margin-right: 2px;
    }
    
    /* Page header */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 0.5rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 992px) {
        .card-body {
            padding: 1rem;
        }
        
        .filter-group {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .quick-date {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    }
    
    @media (max-width: 768px) {
        .container-fluid {
            padding: 0.5rem;
        }
        
        .card {
            margin-bottom: 1rem;
        }
        
        .card-body {
            padding: 0.75rem;
        }
        
        .btn-lg {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
        }
        
        .form-control-lg {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
        }
        
        .filter-group {
            gap: 0.25rem;
        }
        
        .quick-date {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
            margin: 0.1rem;
        }
        
        .page-header {
            padding: 1rem 0;
            margin-bottom: 1rem;
        }
        
        .stats-card {
            margin-bottom: 1rem;
        }
    }
    
    @media (max-width: 576px) {
        .container-fluid {
            padding: 0.25rem;
        }
        
        .card-header {
            padding: 0.75rem 1rem;
        }
        
        .card-body {
            padding: 0.5rem;
        }
        
        .row.g-3 > [class*="col-"] {
            padding: 0.25rem;
        }
        
        .form-control, .form-select {
            font-size: 0.8rem;
            padding: 0.5rem 0.75rem;
        }
        
        .btn {
            font-size: 0.8rem;
            padding: 0.375rem 0.75rem;
        }
        
        .table-responsive {
            font-size: 0.75rem;
        }
        
        .filter-section, .download-section {
            padding: 1rem;
        }
    }
    
    /* Animation and transitions */
    .card, .btn, .form-control, .form-select {
        transition: all 0.3s ease;
    }
    
    /* Focus states */
    .btn:focus, .form-control:focus, .form-select:focus {
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
    
    /* Hover effects */
    .card:hover {
        transform: translateY(-2px);
    }
    
    .btn:hover {
        transform: translateY(-1px);
    }
    
    /* Enhanced border and shadow styles */
    .border-left-primary {
        border-left: 4px solid #4e73df !important;
    }
    .border-left-success {
        border-left: 4px solid #1cc88a !important;
    }
    .border-left-warning {
        border-left: 4px solid #f6c23e !important;
    }
    .border-left-info {
        border-left: 4px solid #36b9cc !important;
    }
    
    /* Utility classes */
    .text-xs {
        font-size: 0.75rem;
    }
    .text-gray-800 {
        color: #5a5c69 !important;
    }
    .text-gray-300 {
        color: #dddfeb !important;
    }
    
    /* Alert improvements */
    .alert {
        border: none;
        border-radius: 0.5rem;
    }
    
    .alert-info {
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        color: #0c5460;
    }
    
    .alert-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
    }
    
    .alert-warning {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        color: #856404;
    }
    
    /* Pagination improvements */
    .pagination {
        margin: 0;
    }
    
    .page-link {
        border: none;
        color: #5a5c69;
        margin: 0 2px;
        border-radius: 0.375rem;
        transition: all 0.3s ease;
    }
    
    .page-link:hover {
        background: linear-gradient(45deg, #4e73df, #6c5ce7);
        color: white;
        transform: translateY(-1px);
    }
    
    .page-item.active .page-link {
        background: linear-gradient(45deg, #4e73df, #6c5ce7);
        border-color: transparent;
    }
    
    /* Improved spacing */
    .mb-4 {
        margin-bottom: 1.5rem !important;
    }
    
    .mb-3 {
        margin-bottom: 1rem !important;
    }
    
    .me-1 {
        margin-right: 0.25rem !important;
    }
    
    .me-2 {
        margin-right: 0.5rem !important;
    }
    
    .me-3 {
        margin-right: 1rem !important;
    }
    
    /* Additional responsive improvements */
    @media (max-width: 480px) {
        .page-header h1 {
            font-size: 1.5rem;
        }
        
        .page-header p {
            font-size: 0.9rem;
        }
        
        .stats-card .card-body {
            padding: 1rem !important;
        }
        
        .stats-card h2 {
            font-size: 1.5rem;
        }
        
        .quick-date {
            font-size: 0.65rem;
            padding: 0.15rem 0.3rem;
        }
        
        .btn-lg {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="page-header text-center mb-4">
        <div class="container">
            <h1 class="h2 mb-2 fw-bold">
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard Pendataan IGD
            </h1>
            <p class="lead mb-0 opacity-75">
                Sistem Informasi Pengantar Pasien - RS Indriati Solo Baru
            </p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4 g-3">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card bg-gradient-primary text-white shadow-lg rounded-4 border-0 h-100 stats-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 rounded-circle bg-white bg-opacity-25 p-3">
                            <i class="fas fa-users fa-2x text-white"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <h6 class="card-title mb-1 text-white-50 fw-normal">Total Pengantar</h6>
                            <h2 class="mb-0 fw-bold" id="total-count">{{ $stats['total'] }}</h2>
                            <small class="text-white-50">Semua Data</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card bg-gradient-info text-white shadow-lg rounded-4 border-0 h-100 stats-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 rounded-circle bg-white bg-opacity-25 p-3">
                            <i class="fas fa-calendar-day fa-2x text-white"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <h6 class="card-title mb-1 text-white-50 fw-normal">Hari Ini</h6>
                            <h2 class="mb-0 fw-bold" id="today-count">{{ $stats['today'] }}</h2>
                            <small class="text-white-50">{{ date('d M Y') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card bg-gradient-warning text-white shadow-lg rounded-4 border-0 h-100 stats-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 rounded-circle bg-white bg-opacity-25 p-3">
                            <i class="fas fa-clock fa-2x text-white"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <h6 class="card-title mb-1 text-white-50 fw-normal">Menunggu</h6>
                            <h2 class="mb-0 fw-bold" id="pending-count">{{ $stats['pending'] }}</h2>
                            <small class="text-white-50">Perlu Verifikasi</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card bg-gradient-success text-white shadow-lg rounded-4 border-0 h-100 stats-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 rounded-circle bg-white bg-opacity-25 p-3">
                            <i class="fas fa-check-circle fa-2x text-white"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <h6 class="card-title mb-1 text-white-50 fw-normal">Terverifikasi</h6>
                            <h2 class="mb-0 fw-bold" id="verified-count">{{ $stats['verified'] }}</h2>
                            <small class="text-white-50">Sudah Disetujui</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold d-flex align-items-center">
                <i class="fas fa-filter me-2"></i> Filter & Pencarian Data
            </h6>
            <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters" aria-expanded="false" aria-controls="advancedFilters">
                <i class="fas fa-sliders-h me-1"></i> Filter Lanjutan
            </button>
        </div>
        <div class="card-body">
            <form id="filter-form">
                <!-- Main Search Row -->
                <div class="row g-3 mb-3">
                    <div class="col-lg-8">
                        <label for="search" class="form-label fw-semibold">
                            <i class="fas fa-search text-primary"></i> Pencarian Cepat
                        </label>
                        <input type="text" class="form-control form-control-lg" id="search" name="search" 
                               placeholder="Cari nama pengantar, nama pasien, nomor HP, atau plat nomor..." 
                               value="{{ request('search') }}">
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Ketik untuk mencari data secara real-time
                        </small>
                    </div>
                    <div class="col-lg-4 d-flex align-items-end">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
                            <button type="submit" class="btn btn-primary btn-lg flex-fill">
                                <i class="fas fa-search me-1"></i> Cari
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-lg flex-fill">
                                <i class="fas fa-sync-alt me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Advanced Filters (Collapsible) -->
                <div class="collapse" id="advancedFilters">
                    <div class="filter-section">
                        <h6 class="text-muted mb-3 d-flex align-items-center">
                            <i class="fas fa-cogs me-2"></i> Filter Lanjutan
                        </h6>
                        
                        <!-- Filter Categories Row -->
                        <div class="row g-3 mb-4">
                            <div class="col-lg-3 col-md-6">
                                <label for="kategori" class="form-label fw-semibold">
                                    <i class="fas fa-tags text-info"></i> Kategori Pengantar
                                </label>
                                <select class="form-select" id="kategori" name="kategori">
                                    <option value="">Semua Kategori</option>
                                    <option value="Polisi" {{ request('kategori') == 'Polisi' ? 'selected' : '' }}>
                                        Polisi
                                    </option>
                                    <option value="Ambulans" {{ request('kategori') == 'Ambulans' ? 'selected' : '' }}>
                                        Ambulans
                                    </option>
                                    <option value="Perorangan" {{ request('kategori') == 'Perorangan' ? 'selected' : '' }}>
                                        Perorangan
                                    </option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label for="status" class="form-label fw-semibold">
                                    <i class="fas fa-check-circle text-success"></i> Status Verifikasi
                                </label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Semua Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                        Menunggu
                                    </option>
                                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>
                                        Terverifikasi
                                    </option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                        Ditolak
                                    </option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label for="jenis_kelamin" class="form-label fw-semibold">
                                    <i class="fas fa-venus-mars text-warning"></i> Jenis Kelamin
                                </label>
                                <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                                    <option value="">Semua</option>
                                    <option value="Laki-laki" {{ request('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>
                                        Laki-laki
                                    </option>
                                    <option value="Perempuan" {{ request('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>
                                        Perempuan
                                    </option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-calendar text-info"></i> Filter Tanggal
                                </label>
                                <div class="date-control-group">
                                    <div class="filter-group">
                                        <input class="form-check-input" type="checkbox" id="today_only" name="today_only" value="1" 
                                               {{ request('today_only') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-normal" for="today_only">
                                            <i class="fas fa-calendar-day"></i> Hari ini
                                        </label>
                                    </div>
                                    <div class="filter-group">
                                        <input class="form-check-input" type="checkbox" id="week_only" name="week_only" value="1" 
                                               {{ request('week_only') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-normal" for="week_only">
                                            <i class="fas fa-calendar-week"></i> Minggu ini
                                        </label>
                                    </div>
                                    <div class="filter-group">
                                        <input class="form-check-input" type="checkbox" id="month_only" name="month_only" value="1" 
                                               {{ request('month_only') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-normal" for="month_only">
                                            <i class="fas fa-calendar-alt"></i> Bulan ini
                                        </label>
                                    </div>
                                    <div class="mt-2">
                                        <input type="date" class="form-control" id="date_specific" name="date_specific" 
                                               value="{{ request('date_specific') }}" max="{{ date('Y-m-d') }}"
                                               placeholder="Pilih tanggal spesifik">
                                        <small class="form-text text-muted mt-1">
                                            <i class="fas fa-calendar"></i> Atau pilih tanggal spesifik
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons and Status -->
                        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" id="view-all-btn" class="btn btn-info">
                                    <i class="fas fa-list me-1"></i> Lihat Semua Data
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="clearAllFilters()">
                                    <i class="fas fa-eraser me-1"></i> Bersihkan Filter
                                </button>
                            </div>
                            <div class="filter-status-indicator">
                                <span id="filter-status">Semua filter tidak aktif</span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Download Section -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold d-flex align-items-center">
                <i class="fas fa-download me-2"></i> Unduh Data
            </h6>
            <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#downloadSection" aria-expanded="false" aria-controls="downloadSection">
                <i class="fas fa-chevron-down me-1"></i> Tampilkan Opsi Unduh
            </button>
        </div>
        <div class="collapse" id="downloadSection">
            <div class="card-body">
                <div class="download-section">
                    <div class="alert alert-info border-0 mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle fa-lg me-3"></i>
                            <div>
                                <strong>Petunjuk:</strong> Pilih rentang tanggal untuk mengunduh data dalam format CSV. 
                                Data yang diunduh akan mencakup semua informasi pengantar sesuai dengan filter yang dipilih.
                            </div>
                        </div>
                    </div>
                    
                    <form id="download-form">
                        @csrf
                        <!-- Date Range and Quick Selection -->
                        <div class="row g-3 mb-4">
                            <div class="col-lg-4">
                                <label for="download_start_date" class="form-label fw-semibold">
                                    <i class="fas fa-calendar-alt text-primary"></i> Tanggal Mulai
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="download_start_date" 
                                       name="start_date" 
                                       max="{{ date('Y-m-d') }}" 
                                       required>
                            </div>
                            <div class="col-lg-4">
                                <label for="download_end_date" class="form-label fw-semibold">
                                    <i class="fas fa-calendar-check text-primary"></i> Tanggal Akhir
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="download_end_date" 
                                       name="end_date" 
                                       max="{{ date('Y-m-d') }}" 
                                       required>
                            </div>
                            <div class="col-lg-4 d-flex align-items-end">
                                <div class="d-grid gap-2 w-100">
                                    <button type="button" id="copy-current-filters" class="btn btn-outline-secondary">
                                        <i class="fas fa-copy me-1"></i> Salin Filter Aktif
                                    </button>
                                    <button type="button" id="preview-download-data" class="btn btn-outline-info">
                                        <i class="fas fa-eye me-1"></i> Preview Data
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Date Selection -->
                        <div class="quick-date-section mb-4">
                            <label class="form-label fw-semibold mb-3">
                                <i class="fas fa-clock text-info"></i> Pilihan Cepat Tanggal
                            </label>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-info btn-sm quick-date" data-days="0">
                                    <i class="fas fa-calendar-day"></i> Hari Ini
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm quick-date" data-days="1">
                                    <i class="fas fa-calendar"></i> Kemarin
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm quick-date" data-days="7">
                                    <i class="fas fa-calendar-week"></i> 7 Hari
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm quick-date" data-days="30">
                                    <i class="fas fa-calendar-alt"></i> 30 Hari
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm quick-date" data-period="current-month">
                                    <i class="fas fa-calendar"></i> Bulan Ini
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm quick-date" data-period="last-month">
                                    <i class="fas fa-calendar-minus"></i> Bulan Lalu
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm quick-date" data-period="last-3-months">
                                    <i class="fas fa-calendar-alt"></i> 3 Bulan
                                </button>
                            </div>
                        </div>

                        <!-- Additional Filters for Download -->
                        <div class="row g-3 mb-4">
                            <div class="col-lg-3">
                                <label for="download_kategori" class="form-label fw-semibold">
                                    <i class="fas fa-tags text-info"></i> Filter Kategori
                                </label>
                                <select class="form-select" id="download_kategori" name="kategori">
                                    <option value="">Semua Kategori</option>
                                    <option value="Polisi">Polisi</option>
                                    <option value="Ambulans">Ambulans</option>
                                    <option value="Perorangan">Perorangan</option>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <label for="download_status" class="form-label fw-semibold">
                                    <i class="fas fa-check-circle text-success"></i> Filter Status
                                </label>
                                <select class="form-select" id="download_status" name="status">
                                    <option value="">Semua Status</option>
                                    <option value="pending">Menunggu</option>
                                    <option value="verified">Terverifikasi</option>
                                    <option value="rejected">Ditolak</option>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <label for="download_jenis_kelamin" class="form-label fw-semibold">
                                    <i class="fas fa-venus-mars text-warning"></i> Filter Gender
                                </label>
                                <select class="form-select" id="download_jenis_kelamin" name="jenis_kelamin">
                                    <option value="">Semua</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <label for="download_search" class="form-label fw-semibold">
                                    <i class="fas fa-search text-primary"></i> Filter Pencarian
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="download_search" 
                                       name="search" 
                                       placeholder="Nama, HP, Plat nomor...">
                            </div>
                        </div>

                        <!-- Data Preview Section -->
                        <div class="download-preview" id="download-preview" style="display: none;">
                            <h6 class="text-info mb-3">
                                <i class="fas fa-chart-bar me-2"></i> Preview Data yang Akan Diunduh
                            </h6>
                            <div class="row g-3 text-center mb-3">
                                <div class="col-md-3 col-6">
                                    <div class="p-3 bg-primary bg-opacity-10 rounded">
                                        <span class="h4 text-primary d-block" id="preview-total">-</span>
                                        <small class="text-muted">Total Record</small>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="p-3 bg-success bg-opacity-10 rounded">
                                        <span class="h4 text-success d-block" id="preview-verified">-</span>
                                        <small class="text-muted">Terverifikasi</small>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="p-3 bg-warning bg-opacity-10 rounded">
                                        <span class="h4 text-warning d-block" id="preview-pending">-</span>
                                        <small class="text-muted">Menunggu</small>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="p-3 bg-danger bg-opacity-10 rounded">
                                        <span class="h4 text-danger d-block" id="preview-rejected">-</span>
                                        <small class="text-muted">Ditolak</small>
                                    </div>
                                </div>
                            </div>
                            <div class="small text-muted text-center">
                                <i class="fas fa-info-circle"></i> 
                                Periode: <span id="preview-period">-</span> | 
                                Filter aktif: <span id="preview-filters">Tidak ada</span>
                            </div>
                        </div>

                        <!-- Download Buttons -->
                        <div class="text-center">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-download me-2"></i> Unduh Data dalam Format Pilihan
                            </h6>
                            <div class="d-flex flex-wrap justify-content-center gap-3">
                                <button type="submit" name="format" value="excel" class="btn btn-primary btn-lg">
                                    <i class="fas fa-file-excel fa-lg me-2"></i>
                                    <span>Unduh Excel</span>
                                    <small class="d-block text-white-50">Format .xlsx dengan styling dan formula</small>
                                </button>
                                <button type="submit" name="format" value="csv" class="btn btn-success btn-lg">
                                    <i class="fas fa-file-csv fa-lg me-2"></i>
                                    <span>Unduh CSV</span>
                                    <small class="d-block text-white-50">Kompatibel dengan Excel & Aplikasi Spreadsheet</small>
                                </button>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i> 
                                    Data akan diunduh sesuai dengan filter dan periode yang dipilih
                                </small>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold d-flex align-items-center">
                <i class="fas fa-table me-2"></i> Data Escort
            </h6>
            <div class="d-flex align-items-center gap-2">
                <small class="text-white-50" id="table-info">Menampilkan data pengantar</small>
                <button class="btn btn-sm btn-outline-light" onclick="location.reload()">
                    <i class="fas fa-sync-alt me-1"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div id="table-container" class="position-relative">
                @include('partials.escort-table', ['escorts' => $escorts])
            </div>
            
            <!-- Pagination -->
            <div id="pagination-container" class="p-3 border-top">
                {{ $escorts->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Loading Spinner -->
<div id="loading" class="d-none position-fixed top-50 start-50 translate-middle" style="z-index: 9999;">
    <div class="card shadow-lg border-0">
        <div class="card-body text-center p-4">
            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h6 class="text-muted mb-0">Memuat data...</h6>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.45/moment-timezone-with-data.min.js"></script>
<script>
$(document).ready(function() {
    // Load initial data
    loadData();
    
    // Initialize filter indicator
    updateFilterIndicator();
    
    // Show advanced filters if any advanced filter has a value on page load
    const advancedFilters = ['#kategori', '#status', '#jenis_kelamin', '#today_only', '#week_only', '#month_only', '#date_specific'];
    const hasAdvancedFilter = advancedFilters.some(selector => 
        $(selector).val() || (['#today_only', '#week_only', '#month_only'].includes(selector) && $(selector).is(':checked'))
    );
    
    if (hasAdvancedFilter) {
        $('#advancedFilters').collapse('show');
        const toggleBtn = $('[data-bs-target="#advancedFilters"]');
        toggleBtn.html('<i class="fas fa-chevron-up"></i> Sembunyikan Filter');
    }
    
    // Handle advanced filter toggle button text
    $('#advancedFilters').on('show.bs.collapse', function () {
        const toggleBtn = $('[data-bs-target="#advancedFilters"]');
        toggleBtn.html('<i class="fas fa-chevron-up me-1"></i> Sembunyikan Filter');
        toggleBtn.removeClass('btn-outline-light').addClass('btn-light');
    });
    
    $('#advancedFilters').on('hide.bs.collapse', function () {
        const toggleBtn = $('[data-bs-target="#advancedFilters"]');
        toggleBtn.html('<i class="fas fa-sliders-h me-1"></i> Filter Lanjutan');
        toggleBtn.removeClass('btn-light').addClass('btn-outline-light');
    });
    
    // Ensure filter status is visible when advanced filters are shown
    $('#advancedFilters').on('shown.bs.collapse', function () {
        updateFilterIndicator();
    });

    // AJAX form submission
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        loadData();
    });
    
    // Auto search on input change with delay
    let searchTimeout;
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            updateFilterIndicator();
            loadData();
        }, 500);
    });
    
    // Auto filter on select change and checkbox/date input change
    $('#kategori, #jenis_kelamin, #status, #today_only, #week_only, #month_only, #date_specific').on('change', function() {
        // Ensure only one date filter is active
        const dateFilters = ['#today_only', '#week_only', '#month_only', '#date_specific'];
        const changedFilter = $(this).attr('id');
        if (dateFilters.includes('#' + changedFilter)) {
            dateFilters.forEach(filter => {
                if (filter !== '#' + changedFilter) {
                    if (filter === '#date_specific') {
                        $(filter).val('');
                    } else {
                        $(filter).prop('checked', false);
                    }
                }
            });
        }
        
        // Update filter button to show active state
        updateFilterIndicator();
        loadData();
        
        // Store filter state in localStorage with GMT+7 timestamp
        localStorage.setItem('filterState', JSON.stringify({
            search: $('#search').val(),
            kategori: $('#kategori').val(),
            jenis_kelamin: $('#jenis_kelamin').val(),
            status: $('#status').val(),
            today_only: $('#today_only').is(':checked') ? 1 : 0,
            week_only: $('#week_only').is(':checked') ? 1 : 0,
            month_only: $('#month_only').is(':checked') ? 1 : 0,
            date_specific: $('#date_specific').val(),
            timestamp: moment().tz('Asia/Jakarta').format()
        }));
    });
    
    // Function to update filter indicator
    function updateFilterIndicator() {
        const activeFilters = [];
        
        if ($('#search').val()) activeFilters.push('Pencarian');
        if ($('#kategori').val()) activeFilters.push('Kategori');
        if ($('#jenis_kelamin').val()) activeFilters.push('Jenis Kelamin');
        if ($('#status').val()) activeFilters.push('Status');
        if ($('#today_only').is(':checked')) activeFilters.push('Hari Ini');
        if ($('#week_only').is(':checked')) activeFilters.push('Minggu Ini');
        if ($('#month_only').is(':checked')) activeFilters.push('Bulan Ini');
        if ($('#date_specific').val()) activeFilters.push('Tanggal Spesifik');
        
        const searchBtn = $('#filter-form button[type="submit"]');
        const filterStatus = $('#filter-status');
        
        if (activeFilters.length > 0) {
            searchBtn.html('<i class="fas fa-search me-1"></i> Cari (' + activeFilters.length + ')');
            searchBtn.removeClass('btn-primary').addClass('btn-success');
            filterStatus.html('<i class="fas fa-filter text-success me-1"></i> ' + activeFilters.length + ' filter aktif: ' + activeFilters.join(', '));
            filterStatus.closest('.filter-status-indicator').removeClass('bg-light').addClass('bg-success bg-opacity-10 border-success');
            
            // Expand advanced filters if any advanced filter is active
            const advancedFilters = ['#kategori', '#status', '#jenis_kelamin', '#today_only', '#week_only', '#month_only', '#date_specific'];
            const hasAdvancedFilter = advancedFilters.some(selector => 
                $(selector).val() || (['#today_only', '#week_only', '#month_only'].includes(selector) && $(selector).is(':checked'))
            );
            
            if (hasAdvancedFilter && !$('#advancedFilters').hasClass('show')) {
                $('#advancedFilters').collapse('show');
            }
        } else {
            searchBtn.html('<i class="fas fa-search me-1"></i> Cari');
            searchBtn.removeClass('btn-success').addClass('btn-primary');
            filterStatus.html('<i class="fas fa-info-circle me-1"></i> Semua filter tidak aktif');
            filterStatus.closest('.filter-status-indicator').removeClass('bg-success bg-opacity-10 border-success').addClass('bg-light');
        }
    }
    
    // Function to clear all filters
    function clearAllFilters() {
        $('#search').val('');
        $('#kategori').val('');
        $('#jenis_kelamin').val('');
        $('#status').val('');
        $('#today_only').prop('checked', false);
        $('#week_only').prop('checked', false);
        $('#month_only').prop('checked', false);
        $('#date_specific').val('');
        updateFilterIndicator();
        loadData();
        
        // Clear filter state in localStorage
        localStorage.removeItem('filterState');
    }
    
    // Make clearAllFilters available globally
    window.clearAllFilters = clearAllFilters;
    
    // Download Section Functionality
    
    // Handle download section toggle button text
    $('#downloadSection').on('show.bs.collapse', function () {
        const toggleBtn = $('[data-bs-target="#downloadSection"]');
        toggleBtn.html('<i class="fas fa-chevron-up me-1"></i> Sembunyikan Opsi Unduh');
        toggleBtn.removeClass('btn-outline-light').addClass('btn-light');
    });
    
    $('#downloadSection').on('hide.bs.collapse', function () {
        const toggleBtn = $('[data-bs-target="#downloadSection"]');
        toggleBtn.html('<i class="fas fa-chevron-down me-1"></i> Tampilkan Opsi Unduh');
        toggleBtn.removeClass('btn-light').addClass('btn-outline-light');
    });
    
    // Quick date selection functionality
    $('.quick-date').on('click', function() {
        const button = $(this);
        const days = button.data('days');
        const period = button.data('period');
        
        let startDate, endDate;
        const today = new Date();
        
        if (period) {
            if (period === 'current-month') {
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = today;
            } else if (period === 'last-month') {
                const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                startDate = lastMonth;
                endDate = new Date(today.getFullYear(), today.getMonth(), 0);
            } else if (period === 'last-3-months') {
                startDate = new Date(today.getFullYear(), today.getMonth() - 3, 1);
                endDate = today;
            }
        } else {
            if (days === 0) {
                startDate = today;
                endDate = today;
            } else if (days === 1) {
                const yesterday = new Date(today.getTime() - (24 * 60 * 60 * 1000));
                startDate = yesterday;
                endDate = yesterday;
            } else {
                startDate = new Date(today.getTime() - (days * 24 * 60 * 60 * 1000));
                endDate = today;
            }
        }
        
        $('#download_start_date').val(formatDateForInput(startDate));
        $('#download_end_date').val(formatDateForInput(endDate));
        
        // Visual feedback
        $('.quick-date').removeClass('btn-info').addClass('btn-outline-info');
        button.removeClass('btn-outline-info').addClass('btn-info');
        
        // Auto-trigger preview if dates are selected
        setTimeout(() => {
            $('.quick-date').removeClass('btn-info').addClass('btn-outline-info');
            if ($('#download_start_date').val() && $('#download_end_date').val()) {
                $('#preview-download-data').trigger('click');
            }
        }, 500);
    });
    
    // Date validation
    $('#download_start_date, #download_end_date').on('change', function() {
        const startDate = $('#download_start_date').val();
        const endDate = $('#download_end_date').val();
        
        if (startDate && endDate) {
            if (new Date(startDate) > new Date(endDate)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanggal Tidak Valid',
                    text: 'Tanggal akhir harus sama atau lebih besar dari tanggal mulai',
                    confirmButtonColor: '#ffc107'
                });
                $(this).val('');
            }
        }
    });
    
    // Copy current filters to download form
    $('#copy-current-filters').on('click', function() {
        const currentFilters = {
            kategori: $('#kategori').val(),
            status: $('#status').val(),
            jenis_kelamin: $('#jenis_kelamin').val(),
            search: $('#search').val()
        };
        
        $('#download_kategori').val(currentFilters.kategori);
        $('#download_status').val(currentFilters.status);
        $('#download_jenis_kelamin').val(currentFilters.jenis_kelamin);
        $('#download_search').val(currentFilters.search);
        
        // Show feedback
        const button = $(this);
        const originalHtml = button.html();
        button.html('<i class="fas fa-check"></i> Filter Disalin!').addClass('btn-success').removeClass('btn-outline-secondary');
        
        setTimeout(() => {
            button.html(originalHtml).removeClass('btn-success').addClass('btn-outline-secondary');
        }, 2000);
    });

    // Preview download data functionality
    $('#preview-download-data').on('click', function() {
        const startDate = $('#download_start_date').val();
        const endDate = $('#download_end_date').val();
        
        if (!startDate || !endDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Tanggal Belum Dipilih',
                text: 'Harap pilih tanggal mulai dan tanggal akhir terlebih dahulu',
                confirmButtonColor: '#ffc107'
            });
            return;
        }

        // Validate date range
        if (new Date(startDate) > new Date(endDate)) {
            Swal.fire({
                icon: 'warning',
                title: 'Tanggal Tidak Valid',
                text: 'Tanggal akhir harus sama atau lebih besar dari tanggal mulai',
                confirmButtonColor: '#ffc107'
            });
            return;
        }

        // Check if date range is too large (more than 1 year)
        const daysDiff = Math.ceil((new Date(endDate) - new Date(startDate)) / (1000 * 60 * 60 * 24));
        if (daysDiff > 365) {
            Swal.fire({
                icon: 'warning',
                title: 'Rentang Tanggal Terlalu Besar',
                text: 'Rentang tanggal maksimal adalah 1 tahun (365 hari)',
                confirmButtonColor: '#ffc107'
            });
            return;
        }

        // Show loading state
        const button = $(this);
        const originalHtml = button.html();
        button.html('<i class="fas fa-spinner fa-spin me-1"></i> Memuat...').prop('disabled', true);
        
        // Prepare data for preview
        const filters = {
            start_date: startDate,
            end_date: endDate,
            kategori: $('#download_kategori').val(),
            status: $('#download_status').val(),
            jenis_kelamin: $('#download_jenis_kelamin').val(),
            search: $('#download_search').val()
        };

        // Make AJAX request to get preview data
        $.ajax({
            url: '{{ route("dashboard") }}',
            method: 'GET',
            data: {
                ...filters,
                preview: true,
                ajax: true
            },
            success: function(response) {
                if (response.status === 'success') {
                    // Update preview section
                    $('#preview-total').text(response.stats?.total || 0);
                    $('#preview-verified').text(response.stats?.verified || 0);
                    $('#preview-pending').text(response.stats?.pending || 0);
                    $('#preview-rejected').text(response.stats?.rejected || 0);
                    
                    // Update period display
                    const startFormatted = formatDateIndonesian(startDate);
                    const endFormatted = formatDateIndonesian(endDate);
                    $('#preview-period').text(`${startFormatted} - ${endFormatted}`);
                    
                    // Update active filters display
                    const activeFilters = [];
                    if (filters.kategori) activeFilters.push(`Kategori: ${filters.kategori}`);
                    if (filters.status) activeFilters.push(`Status: ${filters.status}`);
                    if (filters.jenis_kelamin) activeFilters.push(`Gender: ${filters.jenis_kelamin}`);
                    if (filters.search) activeFilters.push(`Pencarian: ${filters.search}`);
                    
                    $('#preview-filters').text(activeFilters.length > 0 ? activeFilters.join(', ') : 'Tidak ada');
                    
                    // Show preview section with animation
                    $('#download-preview').slideDown();
                    
                    // Success feedback
                    button.html('<i class="fas fa-check me-1"></i> Data Berhasil Dimuat').addClass('btn-success').removeClass('btn-outline-info');
                    
                    setTimeout(() => {
                        button.html(originalHtml).removeClass('btn-success').addClass('btn-outline-info').prop('disabled', false);
                    }, 2000);
                } else {
                    throw new Error(response.message || 'Response status tidak success');
                }
            },
            error: function(xhr) {
                console.error('Preview failed:', xhr);
                let errorMessage = 'Terjadi kesalahan saat memuat preview data. Silakan coba lagi.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = 'Validasi gagal: ' + errors.join(', ');
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Preview',
                    text: errorMessage,
                    confirmButtonColor: '#dc3545'
                });
                
                button.html(originalHtml).prop('disabled', false);
            }
        });
    });
    
    // Handle download form submission
    $('#download-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const startDate = $('#download_start_date').val();
        const endDate = $('#download_end_date').val();
        const format = e.originalEvent.submitter.value; // Get the format from the clicked button
        
        // Validation
        if (!startDate || !endDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Data Tidak Lengkap',
                text: 'Harap pilih tanggal mulai dan tanggal akhir',
                confirmButtonColor: '#ffc107'
            });
            return;
        }
        
        if (new Date(startDate) > new Date(endDate)) {
            Swal.fire({
                icon: 'warning',
                title: 'Tanggal Tidak Valid',
                text: 'Tanggal akhir harus sama atau lebih besar dari tanggal mulai',
                confirmButtonColor: '#ffc107'
            });
            return;
        }
        
        // Check date range (max 1 year)
        const daysDiff = Math.ceil((new Date(endDate) - new Date(startDate)) / (1000 * 60 * 60 * 24));
        if (daysDiff > 365) {
            Swal.fire({
                icon: 'warning',
                title: 'Rentang Tanggal Terlalu Besar',
                text: 'Rentang tanggal maksimal adalah 1 tahun (365 hari)',
                confirmButtonColor: '#ffc107'
            });
            return;
        }
        
        // Show confirmation with details
        const startDateFormatted = formatDateIndonesian(startDate);
        const endDateFormatted = formatDateIndonesian(endDate);
        const formatName = format === 'excel' ? 'Excel' : 'CSV';
        const formatIcon = format === 'excel' ? 'fas fa-file-excel' : 'fas fa-file-csv';
        const formatColor = format === 'excel' ? '#1f4e79' : '#28a745';
        
        Swal.fire({
            title: `Unduh Data ${formatName}`,
            html: `
                <div class="text-left">
                    <p><strong>Rentang Tanggal:</strong><br>
                    ${startDateFormatted} sampai ${endDateFormatted}</p>
                    <p><strong>Periode:</strong> ${daysDiff + 1} hari</p>
                    <p><strong>Format:</strong> ${formatName}</p>
                    <div class="alert alert-info mt-3">
                        <i class="${formatIcon}"></i> 
                        File akan diunduh secara otomatis setelah Anda mengkonfirmasi.
                        ${format === 'excel' ? '<br><small>Format Excel termasuk styling, formatting, dan struktur yang rapi.</small>' : '<br><small>Format CSV cocok untuk import ke berbagai aplikasi spreadsheet.</small>'}
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: formatColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: `<i class="${formatIcon}"></i> Unduh ${formatName}`,
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                popup: 'swal-wide',
                content: 'text-left'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                initiateDownload(format);
            }
        });
    });
    
    // Function to initiate download
    function initiateDownload(format = 'csv') {
        // Show loading
        const formatName = format === 'excel' ? 'Excel' : 'CSV';
        const formatIcon = format === 'excel' ? 'fas fa-file-excel' : 'fas fa-file-csv';
        
        Swal.fire({
            title: `Memproses Download ${formatName}...`,
            html: `
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Sedang memproses data ${formatName.toLowerCase()}, mohon tunggu...</p>
                    <small class="text-muted">Waktu proses tergantung jumlah data yang diunduh</small>
                </div>
            `,
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Prepare form data
        const formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('start_date', $('#download_start_date').val());
        formData.append('end_date', $('#download_end_date').val());
        formData.append('kategori', $('#download_kategori').val());
        formData.append('status', $('#download_status').val());
        formData.append('jenis_kelamin', $('#download_jenis_kelamin').val());
        formData.append('search', $('#download_search').val());
        
        // Choose the correct URL based on format
        const url = format === 'excel' ? '{{ route("dashboard.download.excel") }}' : '{{ route("dashboard.download.csv") }}';
        
        // Use fetch API for download
        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.blob();
        })
        .then(blob => {
            // Create download link
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            
            // Generate filename
            const startDate = $('#download_start_date').val();
            const endDate = $('#download_end_date').val();
            const fileExtension = format === 'excel' ? 'xlsx' : 'csv';
            const filename = `Data_Escort_IGD_${startDate}_sampai_${endDate}_${new Date().getTime()}.${fileExtension}`;
            
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Download Berhasil!',
                html: `
                    <div class="text-center">
                        <i class="${formatIcon} fa-3x text-${format === 'excel' ? 'primary' : 'success'} mb-3"></i>
                        <p>File ${formatName} telah diunduh:</p>
                        <strong>${filename}</strong>
                        ${format === 'excel' ? '<br><small class="text-muted">File Excel dapat dibuka dengan Microsoft Excel, LibreOffice Calc, atau Google Sheets</small>' : '<br><small class="text-muted">File CSV dapat dibuka dengan Excel atau aplikasi spreadsheet lainnya</small>'}
                    </div>
                `,
                timer: 4000,
                showConfirmButton: false
            });
        })
        .catch(error => {
            console.error('Download failed:', error);
            Swal.fire({
                icon: 'error',
                title: 'Download Gagal',
                text: `Terjadi kesalahan saat mengunduh file ${formatName}. Silakan coba lagi.`,
                confirmButtonColor: '#dc3545'
            });
        });
    }
    
    // Utility function to format date for input
    function formatDateForInput(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    // Utility function to format date in Indonesian
    function formatDateIndonesian(dateString) {
        const months = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        
        const date = new Date(dateString);
        const day = date.getDate();
        const month = months[date.getMonth()];
        const year = date.getFullYear();
        
        return `${day} ${month} ${year}`;
    }
    
    // View All button handler
    $('#view-all-btn').on('click', function(e) {
        e.preventDefault();
        
        // Show confirmation dialog with SweetAlert2
        Swal.fire({
            title: 'Lihat Semua Data',
            html: `
                <div class="text-left">
                    <p>Anda akan melihat semua data escort dengan batas maksimal <strong>5000 record</strong>.</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Perhatian:</strong> Memuat data dalam jumlah besar dapat memerlukan waktu loading yang lebih lama.
                    </div>
                    <p>Apakah Anda yakin ingin melanjutkan?</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Lihat Semua',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                popup: 'swal-wide',
                content: 'text-left'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                loadAllData();
            }
        });
    });
    
    // Pagination click handler
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');
        if (url) {
            loadDataFromUrl(url);
        }
    });
    
    function loadData() {
        let formData = $('#filter-form').serialize();
        loadDataFromUrl('{{ route("dashboard") }}?' + formData);
    }
    
    function loadAllData() {
        // Show loading with progress indication
        Swal.fire({
            title: 'Memuat Semua Data...',
            html: `
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Sedang memuat data, mohon tunggu...</p>
                    <small class="text-muted">Proses ini mungkin memerlukan beberapa detik</small>
                </div>
            `,
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
        
        let formData = $('#filter-form').serialize();
        let url = '{{ route("dashboard") }}?' + formData + '&view_all=true';
        
        $.ajax({
            url: url,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                $('#table-container').html(response.html);
                $('#pagination-container').html(response.pagination);
                
                // Update statistics
                $('#total-count').text(response.stats.total);
                $('#today-count').text(response.stats.today);
                $('#pending-count').text(response.stats.pending);
                $('#verified-count').text(response.stats.verified);
                
                // Show success message with record count
                Swal.fire({
                    icon: 'success',
                    title: 'Data Berhasil Dimuat!',
                    html: `
                        <div class="text-center">
                            <p>Menampilkan <strong>${response.record_count || 'semua'}</strong> record data escort</p>
                            ${response.is_limited ? '<small class="text-warning">Data dibatasi maksimal 5000 record untuk performa optimal</small>' : ''}
                        </div>
                    `,
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                console.error('Load all data failed:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Data',
                    text: 'Terjadi kesalahan saat memuat semua data. Silakan coba lagi.',
                    confirmButtonColor: '#dc3545'
                });
            }
        });
    }
    
    function loadDataFromUrl(url) {
        $('#loading').removeClass('d-none');
        
        $.ajax({
            url: url,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                $('#table-container').html(response.html);
                $('#pagination-container').html(response.pagination);
                
                // Update statistics
                $('#total-count').text(response.stats.total);
                $('#today-count').text(response.stats.today);
                $('#pending-count').text(response.stats.pending);
                $('#verified-count').text(response.stats.verified);
                
                $('#loading').addClass('d-none');
            },
            error: function(xhr) {
                $('#loading').addClass('d-none');
                alert('Terjadi kesalahan saat memuat data');
            }
        });
    }
    
    // Status update functionality with SweetAlert2
    $(document).on('click', '.btn-status-update', function(e) {
        e.preventDefault();
        
        const escortId = $(this).data('escort-id');
        const newStatus = $(this).data('status');
        const statusText = $(this).data('status-text');
        const buttonElement = $(this);
        
        // Get escort data from the table row
        const row = buttonElement.closest('tr');
        const escortData = {
            kategori_pengantar: row.find('td:nth-child(2) span.kategori-badge').text().trim(),
            nama_pengantar: row.find('td:nth-child(3)').text().trim(),
            jenis_kelamin: row.find('td:nth-child(4)').text().trim(),
            nomor_hp: row.find('td:nth-child(5)').text().trim(),
            plat_nomor: row.find('td:nth-child(6)').text().trim(),
            nama_pasien: row.find('td:nth-child(7)').text().trim(),
            waktu_masuk: row.find('td:nth-child(9)').text().trim()
        };
        
        // Determine color and icon based on status
        const statusConfig = {
            'verified': {
                confirmButtonColor: '#28a745',
                icon: 'success',
                title: 'Verifikasi Pengantar'
            },
            'rejected': {
                confirmButtonColor: '#dc3545',
                icon: 'warning',
                title: 'Tolak Pengantar'
            },
            'pending': {
                confirmButtonColor: '#ffc107',
                icon: 'question',
                title: 'Kembalikan ke Menunggu'
            }
        };
        
        // Show SweetAlert2 confirmation dialog with escort details
        Swal.fire({
            title: statusConfig[newStatus].title,
            html: `
                <div class="text-left">
                    <h6 class="mb-3">Detail Pengantar:</h6>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Nama Pengantar</strong></td>
                            <td>: ${escortData.nama_pengantar}</td>
                        </tr>
                        <tr>
                            <td><strong>Kategori</strong></td>
                            <td>: ${escortData.kategori_pengantar}</td>
                        </tr>
                        <tr>
                            <td><strong>No. HP</strong></td>
                            <td>: ${escortData.nomor_hp}</td>
                        </tr>
                        <tr>
                            <td><strong>Plat Nomor</strong></td>
                            <td>: ${escortData.plat_nomor}</td>
                        </tr>
                        <tr>
                            <td><strong>Nama Pasien</strong></td>
                            <td>: ${escortData.nama_pasien}</td>
                        </tr>
                        <tr>
                            <td><strong>Waktu Masuk</strong></td>
                            <td>: ${escortData.waktu_masuk}</td>
                        </tr>
                    </table>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i> Apakah Anda yakin ingin mengubah status menjadi <strong>"${statusText}"</strong>?
                    </div>
                </div>
            `,
            icon: statusConfig[newStatus].icon,
            showCancelButton: true,
            confirmButtonColor: statusConfig[newStatus].confirmButtonColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Ubah Status',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                popup: 'swal-wide',
                content: 'text-left'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                updateEscortStatus(escortId, newStatus, buttonElement);
            }
        });
    });
    
    function updateEscortStatus(escortId, status, buttonElement) {
        // Show loading state with SweetAlert2
        Swal.fire({
            title: 'Memproses...',
            text: 'Mohon tunggu sebentar',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: `/escorts/${escortId}/status`,
            type: 'PATCH',
            data: {
                status: status,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Update the status badge in the table
                    const statusBadge = $(`.status-badge-${escortId}`);
                    statusBadge.removeClass('badge-warning badge-success badge-danger')
                            .addClass(response.badge_class)
                            .text(response.status_display);
                    
                    // Update statistics
                    loadData();
                    
                    // Show success message with SweetAlert2
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    
                    // Update button states
                    $(`.btn-status-update[data-escort-id="${escortId}"]`).each(function() {
                        $(this).prop('disabled', false);
                        if ($(this).data('status') === status) {
                            $(this).addClass('btn-secondary').removeClass('btn-outline-success btn-outline-danger')
                                   .prop('disabled', true);
                        } else {
                            $(this).removeClass('btn-secondary').prop('disabled', false);
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Gagal memperbarui status',
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function(xhr) {
                console.error('Status update failed:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: 'Gagal memperbarui status pengantar. Silakan coba lagi.',
                    confirmButtonColor: '#dc3545'
                });
            }
        });
    }
    
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Remove existing alerts
        $('.alert').remove();
        
        // Add new alert at the top of the container
        $('.container-fluid').prepend(alertHtml);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    }
});
</script>
@endpush
@endsection
