@extends('layout.app')

@section('title', 'Dashboard IGD')

@push('styles')
<link href="https://cdnjs.com/cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0 text-gray-800">Dashboard Pendataan IGD</h1>
    <button id="darkModeToggle" class="btn btn-outline-secondary">
        <i class="fas fa-moon"></i>
    </button>
    </div>
    </div>

    <div class="row mb-4 g-3">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-gradient-primary text-white shadow-sm rounded-4 border-0 h-100 stats-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 rounded-circle bg-white bg-opacity-25 p-3">
                            <i class="fas fa-users fa-2x text-white"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <h6 class="card-title mb-1 text-white-50 fw-normal">Total Pengantar</h6>
                            <h2 class="mb-0 fw-bold" id="total-count">{{ $stats['total'] }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-gradient-info text-white shadow-sm rounded-4 border-0 h-100 stats-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 rounded-circle bg-white bg-opacity-25 p-3">
                            <i class="fas fa-calendar-day fa-2x text-white"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <h6 class="card-title mb-1 text-white-50 fw-normal">Hari Ini</h6>
                            <h2 class="mb-0 fw-bold" id="today-count">{{ $stats['today'] }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-gradient-warning text-white shadow-sm rounded-4 border-0 h-100 stats-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 rounded-circle bg-white bg-opacity-25 p-3">
                            <i class="fas fa-clock fa-2x text-white"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <h6 class="card-title mb-1 text-white-50 fw-normal">Menunggu</h6>
                            <h2 class="mb-0 fw-bold" id="pending-count">{{ $stats['pending'] }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-gradient-success text-white shadow-sm rounded-4 border-0 h-100 stats-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 rounded-circle bg-white bg-opacity-25 p-3">
                            <i class="fas fa-check-circle fa-2x text-white"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <h6 class="card-title mb-1 text-white-50 fw-normal">Terverifikasi</h6>
                            <h2 class="mb-0 fw-bold" id="verified-count">{{ $stats['verified'] }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-success">
                <i class="fas fa-filter me-2"></i>Filter & Pencarian
            </h6>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters" aria-expanded="false" aria-controls="advancedFilters">
                <i class="fas fa-sliders-h me-1"></i> Filter Lanjutan
            </button>
        </div>
        <div class="card-body">
            <form id="filter-form">
                <div class="row g-3 mb-3">
                    <div class="col-lg-8">
                        <label for="search" class="form-label">Pencarian Cepat</label>
                        <input type="text" class="form-control" id="search" name="search"
                               placeholder="Cari nama pengantar, nama pasien, nomor HP, atau plat nomor..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-lg-4 d-flex align-items-end">
                        <div class="d-grid gap-2 d-md-flex w-100">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-search me-1"></i> Cari
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary flex-fill">
                                <i class="fas fa-sync-alt me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>

                <div class="collapse" id="advancedFilters">
                    <hr>
                    <div class="row g-3">
                         <div class="col-lg-3 col-md-6">
                            <label for="kategori" class="form-label">Kategori Pengantar</label>
                            <select class="form-select" id="kategori" name="kategori">
                                <option value="">Semua Kategori</option>
                                <option value="Polisi" {{ request('kategori') == 'Polisi' ? 'selected' : '' }}>Polisi</option>
                                <option value="Ambulans" {{ request('kategori') == 'Ambulans' ? 'selected' : '' }}>Ambulans</option>
                                <option value="Perorangan" {{ request('kategori') == 'Perorangan' ? 'selected' : '' }}>Perorangan</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label for="status" class="form-label">Status Verifikasi</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Terverifikasi</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                                <option value="">Semua</option>
                                <option value="Laki-laki" {{ request('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ request('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">Filter Tanggal Cepat</label>
                            <div class="d-flex flex-wrap gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="today_only" name="today_only" value="1" {{ request('today_only') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="today_only">Hari ini</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="week_only" name="week_only" value="1" {{ request('week_only') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="week_only">Minggu ini</label>
                                </div>
                                 <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="month_only" name="month_only" value="1" {{ request('month_only') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="month_only">Bulan ini</label>
                                </div>
                            </div>
                             <div class="mt-2">
                                <label for="date_specific" class="form-label small">Atau Pilih Tanggal Spesifik</label>
                                <input type="date" class="form-control form-control-sm" id="date_specific" name="date_specific" value="{{ request('date_specific') }}" max="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-success">
                <i class="fas fa-download me-2"></i>Unduh Data
            </h6>
             <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#downloadSection" aria-expanded="false" aria-controls="downloadSection">
                <i class="fas fa-chevron-down me-1"></i> Tampilkan Opsi
            </button>
        </div>
        <div class="collapse" id="downloadSection">
            <div class="card-body">
                 <div class="alert alert-info border-left-info">
                    <strong>Petunjuk:</strong> Pilih rentang tanggal dan filter opsional untuk mengunduh data dalam format Excel atau CSV.
                </div>
                <form id="download-form">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-lg-4">
                            <label for="download_start_date" class="form-label fw-semibold">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="download_start_date" name="start_date" max="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-lg-4">
                            <label for="download_end_date" class="form-label fw-semibold">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="download_end_date" name="end_date" max="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-lg-4 d-flex align-items-end">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-outline-info quick-date" data-days="0">Hari Ini</button>
                                <button type="button" class="btn btn-sm btn-outline-info quick-date" data-days="7">7 Hari</button>
                                <button type="button" class="btn btn-sm btn-outline-info quick-date" data-period="current-month">Bulan Ini</button>
                                <button type="button" class="btn btn-sm btn-outline-info quick-date" data-period="last-month">Bulan Lalu</button>
                            </div>
                        </div>
                    </div>
                     <div class="row g-3 mb-3">
                        <div class="col-lg-3">
                            <label for="download_kategori" class="form-label">Filter Kategori</label>
                            <select class="form-select" id="download_kategori" name="kategori">
                                <option value="">Semua</option>
                                <option value="Polisi">Polisi</option>
                                <option value="Ambulans">Ambulans</option>
                                <option value="Perorangan">Perorangan</option>
                            </select>
                        </div>
                         <div class="col-lg-3">
                            <label for="download_status" class="form-label">Filter Status</label>
                            <select class="form-select" id="download_status" name="status">
                                <option value="">Semua</option>
                                <option value="pending">Menunggu</option>
                                <option value="verified">Terverifikasi</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label for="download_jenis_kelamin" class="form-label">Filter Gender</label>
                            <select class="form-select" id="download_jenis_kelamin" name="jenis_kelamin">
                                <option value="">Semua</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                         <div class="col-lg-3">
                            <label for="download_search" class="form-label">Filter Pencarian</label>
                            <input type="text" class="form-control" id="download_search" name="search" placeholder="Nama, HP, dll...">
                        </div>
                     </div>
                     <div class="d-flex gap-2 mb-3">
                        <button type="button" id="copy-current-filters" class="btn btn-secondary">
                            <i class="fas fa-copy me-1"></i> Salin Filter Aktif
                        </button>
                        <button type="button" id="preview-download-data" class="btn btn-info">
                            <i class="fas fa-eye me-1"></i> Preview Data
                        </button>
                     </div>
                     <div class="border rounded p-3" id="download-preview" style="display: none;">
                        <h6 class="text-info mb-3">Preview Data yang Akan Diunduh</h6>
                        <div class="row g-3 text-center mb-2">
                            <div class="col-md-3 col-6"><span class="h4 d-block" id="preview-total">-</span><small>Total</small></div>
                            <div class="col-md-3 col-6"><span class="h4 d-block" id="preview-verified">-</span><small>Terverifikasi</small></div>
                            <div class="col-md-3 col-6"><span class="h4 d-block" id="preview-pending">-</span><small>Menunggu</small></div>
                            <div class="col-md-3 col-6"><span class="h4 d-block" id="preview-rejected">-</span><small>Ditolak</small></div>
                        </div>
                        <div class="small text-muted text-center">Periode: <span id="preview-period">-</span></div>
                     </div>
                    <hr>
                    <div class="text-center">
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <button type="submit" name="format" value="excel" class="btn btn-primary">
                                <i class="fas fa-file-excel me-2"></i>Unduh Excel
                            </button>
                            <button type="submit" name="format" value="csv" class="btn btn-success">
                                <i class="fas fa-file-csv me-2"></i>Unduh CSV
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-success">Data Pengantar Pasien</h6>
            <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt me-1"></i> Refresh Tabel
            </button>
        </div>
        <div class="card-body">
            <div id="table-container">
                @include('partials.escort-table', ['escorts' => $escorts])
            </div>
            <div id="pagination-container" class="mt-3">
                {{ $escorts->links() }}
            </div>
        </div>
    </div>
</div>

<div id="loading" class="d-none">
    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.45/moment-timezone-with-data.min.js"></script>
<script>
$(document).ready(function() {
    // Note: The JavaScript logic from the original file remains largely unchanged 
    // as it controls functionality tied to element IDs and classes which have been preserved.
    // The visual changes are handled by the new CSS.
    
    // Load initial data
    loadData();
    
    // Show advanced filters if any advanced filter has a value on page load
    const advancedFilters = ['#kategori', '#status', '#jenis_kelamin', '#today_only', '#week_only', '#month_only', '#date_specific'];
    const hasAdvancedFilter = advancedFilters.some(selector => 
        $(selector).val() || (['#today_only', '#week_only', '#month_only'].includes(selector) && $(selector).is(':checked'))
    );
    
    if (hasAdvancedFilter) {
        $('#advancedFilters').collapse('show');
    }
    
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
            loadData();
        }, 500);
    });
    
    // Auto filter on select change and checkbox/date input change
    $('#kategori, #jenis_kelamin, #status, #today_only, #week_only, #month_only, #date_specific').on('change', function() {
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
        loadData();
    });
    
    // Download Section Functionality
    
    // Quick date selection
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
            }
        } else {
            if (days === 0) {
                startDate = today;
                endDate = today;
            } else {
                startDate = new Date(today.getTime() - (days * 24 * 60 * 60 * 1000));
                endDate = today;
            }
        }
        
        $('#download_start_date').val(formatDateForInput(startDate));
        $('#download_end_date').val(formatDateForInput(endDate));
    });
    
    // Date validation
    $('#download_start_date, #download_end_date').on('change', function() {
        const startDate = $('#download_start_date').val();
        const endDate = $('#download_end_date').val();
        
        if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
            Swal.fire('Tanggal Tidak Valid', 'Tanggal akhir harus sama atau lebih besar dari tanggal mulai', 'warning');
            $(this).val('');
        }
    });

    // Copy current filters to download form
    $('#copy-current-filters').on('click', function() {
        $('#download_kategori').val($('#kategori').val());
        $('#download_status').val($('#status').val());
        $('#download_jenis_kelamin').val($('#jenis_kelamin').val());
        $('#download_search').val($('#search').val());
        
        const button = $(this);
        const originalHtml = button.html();
        button.html('<i class="fas fa-check"></i> Disalin!').addClass('btn-success').removeClass('btn-secondary');
        setTimeout(() => {
            button.html(originalHtml).removeClass('btn-success').addClass('btn-secondary');
        }, 2000);
    });

    // Preview download data
    $('#preview-download-data').on('click', function() {
        const startDate = $('#download_start_date').val();
        const endDate = $('#download_end_date').val();
        
        if (!startDate || !endDate) {
            Swal.fire('Tanggal Belum Dipilih', 'Harap pilih tanggal mulai dan tanggal akhir', 'warning');
            return;
        }

        const button = $(this);
        const originalHtml = button.html();
        button.html('<i class="fas fa-spinner fa-spin me-1"></i> Memuat...').prop('disabled', true);
        
        const filters = {
            start_date: startDate,
            end_date: endDate,
            kategori: $('#download_kategori').val(),
            status: $('#download_status').val(),
            jenis_kelamin: $('#download_jenis_kelamin').val(),
            search: $('#download_search').val()
        };

        $.ajax({
            url: '{{ route("dashboard") }}',
            method: 'GET',
            data: { ...filters, preview: true, ajax: true },
            success: function(response) {
                if (response.status === 'success') {
                    $('#preview-total').text(response.stats?.total || 0);
                    $('#preview-verified').text(response.stats?.verified || 0);
                    $('#preview-pending').text(response.stats?.pending || 0);
                    $('#preview-rejected').text(response.stats?.rejected || 0);
                    $('#preview-period').text(`${formatDateIndonesian(startDate)} - ${formatDateIndonesian(endDate)}`);
                    $('#download-preview').slideDown();
                }
                button.html(originalHtml).prop('disabled', false);
            },
            error: function() {
                Swal.fire('Gagal', 'Terjadi kesalahan saat memuat preview data.', 'error');
                button.html(originalHtml).prop('disabled', false);
            }
        });
    });
    
    // Handle download form submission
    $('#download-form').on('submit', function(e) {
        e.preventDefault();
        const startDate = $('#download_start_date').val();
        const endDate = $('#download_end_date').val();
        if (!startDate || !endDate) {
            Swal.fire('Data Tidak Lengkap', 'Harap pilih tanggal mulai dan tanggal akhir', 'warning');
            return;
        }
        
        const format = e.originalEvent.submitter.value;
        initiateDownload(format);
    });

    function initiateDownload(format = 'csv') {
        Swal.fire({
            title: 'Memproses Download...',
            text: 'Mohon tunggu sebentar',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => Swal.showLoading()
        });

        const formData = new FormData($('#download-form')[0]);
        const url = format === 'excel' ? '{{ route("dashboard.download.excel") }}' : '{{ route("dashboard.download.csv") }}';
        
        fetch(url, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.blob();
        })
        .then(blob => {
            const downloadUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = downloadUrl;
            const fileExtension = format === 'excel' ? 'xlsx' : 'csv';
            a.download = `Data_Escort_IGD_${$('#download_start_date').val()}_to_${$('#download_end_date').val()}.${fileExtension}`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            Swal.fire('Download Berhasil!', `File ${format.toUpperCase()} telah diunduh.`, 'success');
        })
        .catch(error => {
            console.error('Download failed:', error);
            Swal.fire('Download Gagal', 'Terjadi kesalahan saat mengunduh file.', 'error');
        });
    }

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
    
    function loadDataFromUrl(url) {
        $('#loading').removeClass('d-none');
        $.ajax({
            url: url,
            type: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(response) {
                $('#table-container').html(response.html);
                $('#pagination-container').html(response.pagination);
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
    
    // Status update functionality
    $(document).on('click', '.btn-status-update', function(e) {
        e.preventDefault();
        const escortId = $(this).data('escort-id');
        const newStatus = $(this).data('status');
        const statusText = $(this).data('status-text');
        const buttonElement = $(this);
        
        const row = buttonElement.closest('tr');
        const escortData = {
            nama_pengantar: row.find('td:nth-child(3)').text().trim(),
            nama_pasien: row.find('td:nth-child(7)').text().trim(),
        };

        Swal.fire({
            title: `Ubah Status ke "${statusText}"?`,
            html: `Anda akan mengubah status untuk pengantar <strong>${escortData.nama_pengantar}</strong> (pasien: ${escortData.nama_pasien}). Lanjutkan?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Ubah Status',
            cancelButtonText: 'Batal',
            customClass: { popup: 'swal-wide' }
        }).then((result) => {
            if (result.isConfirmed) {
                updateEscortStatus(escortId, newStatus);
            }
        });
    });

    function updateEscortStatus(escortId, status) {
        Swal.fire({
            title: 'Memproses...',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => Swal.showLoading()
        });
        
        $.ajax({
            url: `/escorts/${escortId}/status`,
            type: 'PATCH',
            data: {
                status: status,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Berhasil!', response.message, 'success');
                    loadData(); // Reload data to reflect changes
                } else {
                    Swal.fire('Gagal', 'Gagal memperbarui status.', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Terjadi kesalahan. Silakan coba lagi.', 'error');
            }
        });
    }

    // Utility functions
    function formatDateForInput(date) {
        return date.toISOString().split('T')[0];
    }

    function formatDateIndonesian(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    }
});
</script>
@endpush
@endsection