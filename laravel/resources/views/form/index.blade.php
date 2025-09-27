@extends('layout.app')

@section('title','Formulir Pengantar Pasien')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
/* Global */
body {
  background: whitesmoke;
  min-height: 100vh;
  margin: 0;
  padding: 2rem 1rem;
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

/* Form Container & Card */
.form-container {
  max-width: 800px;
  margin: 0 auto;
  position: relative;
}
.form-container::before {
  content: '';
  position: absolute;
  inset: -5px;
  border-radius: 1rem;
  z-index: -1;
  opacity: 0.7;
  filter: blur(10px);
}
.form-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  overflow: hidden;
  animation: slideUp 0.6s ease-out forwards;
}
@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Header */
.card-header {
  background: #fff;
  padding: 2rem;
  border: none;
  position: relative;
  overflow: hidden;
}
.card-header::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 100%);
  z-index: 1;
}
.form-title {
  color: #fff;
  font-weight: 600;
  font-size: 1.75rem;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 1rem;
  position: relative;
  z-index: 2;
  text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
.form-subtitle {
  color: #858796;
  font-size: 0.875rem;
  margin: 0.5rem 0 0rem;
  position: relative;
  z-index: 2;
}
.logo {
    height:150px;
    width:auto;
    margin-right:10px;
}

/* Body & Section */
.card-body {
  padding: 2.5rem;
  border-radius: 1rem;
  background: #fff;
}

/* Form Controls */
.form-group { margin-bottom: 1.5rem; }
.form-label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  font-size: 0.95rem;
  color: #475569;
}
.form-control,
.form-select {
  display: block;
  width: 100%;
  padding: 0.75rem 1rem;
  font-size: 0.95rem;
  line-height: 1.5;
  color: #0f172a;
  background: rgba(255,255,255,0.9);
  border: 2px solid #e2e8f0;
  border-radius: 0.75rem;
  transition: all 0.2s ease;
}
.form-control:focus,
.form-select:focus {
  border-color: #0ea5e9;
  box-shadow: 0 0 0 3px rgba(14,165,233,0.1);
  outline: none;
}
.form-control::placeholder { color: #94a3b8; }
.form-select {
  padding-right: 2.5rem;
  background-image: url("data:image/svg+xml,..."); /* arrow svg */
  background-position: right 1rem center;
  background-size: 1.5em 1.5em;
}

/* Input Group */
.input-group { display: flex; width: 100%; }
.input-group-text {
  background: #f8fafc;
  border: 2px solid #e2e8f0;
  border-right: 0;
  border-radius: 0.75rem 0 0 0.75rem;
  color: #64748b;
  padding: 0.75rem 1rem;
}
.input-group .form-control {
  border-left: 0;
  border-radius: 0 0.75rem 0.75rem 0;
}

/* File Input */
.form-control[type="file"] {
  padding: 0.5rem;
  background: #f8fafc;
}
.form-control[type="file"]::file-selector-button {
  background: #fff;
  border: 2px solid #e2e8f0;
  border-radius: 0.5rem;
  padding: 0.5rem 1rem;
  color: #475569;
  font-weight: 500;
  margin-right: 1rem;
  transition: all 0.2s ease;
}
.form-control[type="file"]::file-selector-button:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

/* Buttons */
.btn {
  display: inline-block;
  font-weight: 600;
  line-height: 1.5;
  cursor: pointer;
  user-select: none;
  padding: 0.875rem 1.75rem;
  font-size: 1rem;
  border-radius: 0.75rem;
  transition: all 0.2s ease;
  text-align: center;
}
.btn-primary {
  background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
  color: #fff;
  border: none;
  box-shadow: 0 4px 6px -1px rgba(14,165,233,0.2);
}
.btn-primary:hover {
  background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
  transform: translateY(-1px);
  box-shadow: 0 6px 8px -1px rgba(14,165,233,0.3);
}
.btn-secondary {
  background: #f1f5f9;
  border: 2px solid #e2e8f0;
  color: #475569;
}
.btn-secondary:hover {
  background: #e2e8f0;
  color: #334155;
}

/* Validation */
.form-control.is-invalid,
.form-select.is-invalid {
  border-color: #ef4444;
  box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
}
.invalid-feedback {
  display: block;
  color: #ef4444;
  font-size: 0.875rem;
  margin-top: 0.375rem;
}

/* File Input Wrapper */
.file-input-wrapper { position: relative; width: 100%; }
.file-input { position: absolute; opacity: 0; width: 100%; height: 100%; cursor: pointer; }
.file-input-display {
  display: flex;
  align-items: center;
  padding: 0.75rem;
  border: 2px dashed #d1d3e2;
  border-radius: 0.35rem;
  background: #f8f9fc;
  cursor: pointer;
  transition: all 0.3s ease;
}
.file-input-display:hover { border-color: #4e73df; background: #eaecf4; }
.file-input-display.has-file { border-style: solid; border-color: #1cc88a; background: #f0fff4; }
.file-icon { font-size: 1.25rem; margin-right: 0.75rem; color: #858796; }
.file-text { flex: 1; font-size: 0.875rem; color: #858796; }
.file-input-display.has-file .file-text { color: #1cc88a; }

/* Grid */
.form-row { display: flex; flex-wrap: wrap; margin: 0 -0.75rem; }
.form-row > .col { flex: 1; max-width: 100%; padding: 0 0.75rem; }
.col-md-6 { flex: 0 0 50%; max-width: 50%; }
@media (max-width: 768px) {
  .col-md-6 { flex: 0 0 100%; max-width: 100%; }
  .form-card { margin: 1rem; }
}

/* Loading Button */
.loading-spinner { display: none; }
.btn-primary.loading .btn-text { display: none; }
.btn-primary.loading .loading-spinner { display: inline-block; }

.steps-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    margin-bottom: 2rem;
}
.step-item {
    display: flex;
    align-items: center;
    flex-direction: column;
    text-align: center;
}
.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e2e8f0;
    color: #475569;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-bottom: 0.5rem;
}
.step-title {
    font-size: 0.875rem;
    color: #475569;
    font-weight: 500;
}
.step-item.active .step-circle {
    background-color: #0ea5e9;
    color: #fff;
    box-shadow: 0 0 0 4px rgba(14,165,233,0.2);
}
.step-item.active .step-title {
    color: #0f172a;
    font-weight: 600;
}
.step-line {
    flex: 1;
    height: 2px;
    background-color: #e2e8f0;
    margin: 0 1rem;
    transform: translateY(-1rem);
}
.step-item.active ~ .step-line {
    background-color: #0ea5e9;
}
.step-item.active ~ .step-item .step-circle {
    background-color: #e2e8f0;
}
.step-content {
    display: none;
}
.step-content.active {
    display: block;
}
.form-navigation {
    display: flex;
    justify-content: space-between;
    margin-top: 2rem;
}
</style>

@endpush

@section('content')
<div class="container-fluid">
    <div class="form-container">
        <div class="card shadow mb-4">
            <div class="card-header pt-2 pb-1 border-left-primary align-items-center">
                <!-- Logo -->
                <div class="text-center mt-3">
                <img src="{{ asset('Rumah_Sakit_Indriati.webp') }}" alt="Logo" 
                class="logo">
                </div>
                <h2 class="mx-3 font-weight-bold text-black form-title pt-3">
                    <i class="fas fa-user-plus"></i> Form Data Pengantar Pasien
                </h2>
                <p class="form-subtitle mx-3">Silakan lengkapi data pengantar pasien di bawah ini</p>
            </div>
            <hr>
            <div class="card-body">
                @include('layout.steps')
                <!-- Session-based success/error messages -->
                @if(isset($successMessage) && $successMessage)
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> {{ $successMessage }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(isset($errorMessage) && $errorMessage)
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> {{ $errorMessage }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" id="escortForm" enctype="multipart/form-data">
                    <div class="step-content active" data-step="1">
                        <div class="form-group">
                            <label for="kategori_pengantar" class="form-label">
                                <i class="fas fa-tags"></i> Kategori Pengantar
                            </label>
                            <select class="form-select" id="kategori_pengantar" name="kategori_pengantar" required>
                                <option value="">Pilih kategori pengantar...</option>
                                <option value="Polisi">Polisi</option>
                                <option value="Ambulans">Ambulans</option>
                                <option value="Perorangan">Perorangan</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="nama_pengantar" class="form-label">
                                <i class="fas fa-user"></i> Nama Pengantar
                            </label>
                            <div class="input-group">
                                <div class="input-group-text">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <input type="text" class="form-control" id="nama_pengantar" name="nama_pengantar" 
                                       placeholder="Masukkan nama lengkap pengantar" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="jenis_kelamin" class="form-label">
                                <i class="fas fa-venus-mars"></i> Jenis Kelamin
                            </label>
                            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                <option value="">Pilih jenis kelamin...</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="nomor_hp" class="form-label">
                                <i class="fas fa-phone"></i> Nomor HP
                            </label>
                            <div class="input-group">
                                <div class="input-group-text">
                                    +62
                                </div>
                                <input type="tel" class="form-control" id="nomor_hp" name="nomor_hp" 
                                       placeholder="Contoh: 81234567890" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="plat_nomor" class="form-label">
                                <i class="fas fa-car"></i> Plat Nomor (Opsional)
                            </label>
                            <div class="input-group">
                                <div class="input-group-text">
                                    <i class="fas fa-hashtag"></i>
                                </div>
                                <input type="text" class="form-control" id="plat_nomor" name="plat_nomor" 
                                       placeholder="Contoh: AB 1234 CD">
                            </div>
                        </div>
                        <div class="form-navigation">
                            <button type="button" class="btn btn-primary next-step">Berikutnya <i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>

                    <div class="step-content" data-step="2">
                        <div class="form-group">
                            <label for="nama_pasien" class="form-label">
                                <i class="fas fa-user-injured"></i> Nama Pasien
                            </label>
                            <div class="input-group">
                                <div class="input-group-text">
                                    <i class="fas fa-id-card-alt"></i>
                                </div>
                                <input type="text" class="form-control" id="nama_pasien" name="nama_pasien" 
                                       placeholder="Masukkan nama lengkap pasien" required>
                            </div>
                        </div>
                        <div class="form-navigation">
                            <button type="button" class="btn btn-secondary prev-step"><i class="fas fa-arrow-left"></i> Kembali</button>
                            <button type="button" class="btn btn-primary next-step">Berikutnya <i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>

                    <div class="step-content" data-step="3">
                        <div class="form-group">
                            <label for="foto_pengantar" class="form-label">
                                <i class="fas fa-camera"></i> Foto Pengantar
                            </label>
                            <div class="file-input-wrapper">
                                <input type="file" class="file-input" id="foto_pengantar" name="foto_pengantar" 
                                       accept="image/*" required>
                                <div class="file-input-display">
                                    <i class="fas fa-cloud-upload-alt file-icon"></i>
                                    <span class="file-text">Klik untuk memilih foto atau drag & drop</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-navigation">
                            <button type="button" class="btn btn-secondary prev-step"><i class="fas fa-arrow-left"></i> Kembali</button>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <span class="btn-text">
                                    <i class="fas fa-paper-plane"></i> Kirim Data
                                </span>
                                <span class="loading-spinner">
                                    <i class="fas fa-spinner fa-spin"></i> Memproses...
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
<script>
    $(document).ready(function() {
        let currentStep = 1;

        function showStep(step) {
            $('.step-content').removeClass('active');
            $(`.step-content[data-step="${step}"]`).addClass('active');
            $('.step-item').removeClass('active');
            $(`.step-item[data-step="${step}"]`).addClass('active');
            currentStep = step;
        }

        $('.next-step').on('click', function() {
            // Basic validation for current step
            let isValid = true;
            $(`.step-content[data-step="${currentStep}"]`).find('input[required], select[required]').each(function() {
                if (!$(this).val()) {
                    isValid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (isValid) {
                showStep(currentStep + 1);
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Harap isi semua kolom yang wajib diisi.',
                    confirmButtonColor: '#ffc107'
                });
            }
        });

        $('.prev-step').on('click', function() {
            showStep(currentStep - 1);
        });

        // File input handling
        $('#foto_pengantar').on('change', function() {
            const file = this.files[0];
            const display = $('.file-input-display');
            const text = $('.file-text');
            
            if (file) {
                display.addClass('has-file');
                text.text(`File dipilih: ${file.name}`);
            } else {
                display.removeClass('has-file');
                text.text('Klik untuk memilih foto atau drag & drop');
            }
        });

        // Drag and drop functionality
        $('.file-input-display').on('dragover', function(e) {
            e.preventDefault();
            $(this).css('border-color', '#667eea');
        });

        $('.file-input-display').on('dragleave', function(e) {
            e.preventDefault();
            $(this).css('border-color', '#e9ecef');
        });

        $('.file-input-display').on('drop', function(e) {
            e.preventDefault();
            $(this).css('border-color', '#e9ecef');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                $('#foto_pengantar')[0].files = files;
                $('#foto_pengantar').trigger('change');
            }
        });

        // Phone number formatting
        $('#nomor_hp').on('input', function() {
            let value = $(this).val().replace(/\D/g, '');
            if (value.startsWith('0')) {
                value = value;
            } else if (value.startsWith('62')) {
                value = '0' + value.substring(2);
            }
            $(this).val(value);
        });

        // Plate number formatting
        $('#plat_nomor').on('input', function() {
            let value = $(this).val().toUpperCase();
            $(this).val(value);
        });

        // Form submission with enhanced session tracking
        $('#escortForm').on('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = $('#submitBtn');
            const form = this;
            
            // Store form submission attempt in localStorage for tracking
            const submissionAttempt = {
                timestamp: new Date().toISOString(),
                category: $('#kategori_pengantar').val(),
                name: $('#nama_pengantar').val(),
                patient: $('#nama_pasien').val()
            };
            localStorage.setItem('lastSubmissionAttempt', JSON.stringify(submissionAttempt));
            
            // Disable button and show loading
            submitBtn.addClass('loading').prop('disabled', true);
            
            // Show loading alert
            Swal.fire({
                title: 'Memproses Data',
                text: 'Mohon tunggu sebentar...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            // Convert image to base64 before sending
            const fileInput = $('#foto_pengantar')[0];
            if (fileInput.files && fileInput.files[0]) {
                const file = fileInput.files[0];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Create FormData with base64 image
                    const formData = new FormData();
                    
                    // Add all form fields except the file input
                    formData.append('kategori_pengantar', $('#kategori_pengantar').val());
                    formData.append('nama_pengantar', $('#nama_pengantar').val());
                    formData.append('jenis_kelamin', $('#jenis_kelamin').val());
                    formData.append('nomor_hp', $('#nomor_hp').val());
                    formData.append('plat_nomor', $('#plat_nomor').val());
                    formData.append('nama_pasien', $('#nama_pasien').val());
                    
                    // Add base64 image data
                    formData.append('foto_pengantar_base64', e.target.result);
                    
                    // Add image info for validation
                    formData.append('foto_pengantar_info[name]', file.name);
                    formData.append('foto_pengantar_info[size]', file.size);
                    formData.append('foto_pengantar_info[type]', file.type);
                    
                    // Submit the form with base64 data
                    submitFormData(formData, submitBtn);
                };
                
                reader.onerror = function() {
                    submitBtn.removeClass('loading').prop('disabled', false);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Gagal membaca file gambar. Silakan coba lagi.',
                        confirmButtonColor: '#dc3545'
                    });
                };
                
                reader.readAsDataURL(file);
            } else {
                // No file selected
                submitBtn.removeClass('loading').prop('disabled', false);
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Silakan pilih foto pengantar terlebih dahulu.',
                    confirmButtonColor: '#ffc107'
                });
            }
        });

        // Function to submit form data
        function submitFormData(formData, submitBtn) {
            
            $.ajax({
                url: '/api/escort',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    submitBtn.removeClass('loading').prop('disabled', false);
                    
                    // Store successful submission data
                    const submissionSuccess = {
                        timestamp: new Date().toISOString(),
                        submission_id: response.submission_id,
                        escort_id: response.data ? response.data.id : null,
                        session_id: response.session_id,
                        message: response.message
                    };
                    localStorage.setItem('lastSuccessfulSubmission', JSON.stringify(submissionSuccess));
                    
                    // Update submission counter
                    let submissionCount = parseInt(localStorage.getItem('submissionCount') || '0');
                    localStorage.setItem('submissionCount', (submissionCount + 1).toString());
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        html: `
                            <div>
                                <p>Data berhasil disimpan</p>
                                <small class="text-muted">Data pengantar pasien telah berhasil disimpan dan akan diverifikasi oleh petugas.</small><br><small class="text-muted">Silahkan tunggu verifikasi dari petugas untuk proses selanjutnya.</small>
                            </div>
                        `,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#16aa40ff',
                        timer: 5000,
                        timerProgressBar: true,
                        showClass: {
                            popup: 'animate__animated animate__fadeInDown'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutUp'
                        }
                    }).then(() => {
                        // Reset form
                        $('#escortForm')[0].reset();
                        $('.file-input-display').removeClass('has-file');
                        $('.file-text').text('Klik untuk memilih foto atau drag & drop');
                        
                        // Clear form validation states
                        $('.form-control, .form-select').removeClass('is-valid is-invalid');
                        
                        // Go to first step
                        showStep(1);
                    });
                    
                    // Optional: Show submission statistics
                    console.log('Submission Statistics:', {
                        total_submissions: submissionCount + 1,
                        session_id: response.session_id,
                        api_submissions_count: response.meta ? response.meta.api_submissions_count : null
                    });
                },
                error: function (xhr) {
                    submitBtn.removeClass('loading').prop('disabled', false);
                    
                    // Store error details
                    const submissionError = {
                        timestamp: new Date().toISOString(),
                        status: xhr.status,
                        response: xhr.responseJSON,
                        submission_id: xhr.responseJSON ? xhr.responseJSON.submission_id : null
                    };
                    localStorage.setItem('lastSubmissionError', JSON.stringify(submissionError));
                    
                    let errorMessage = 'Terjadi kesalahan saat memproses data';
                    let errorDetails = '';
                    
                    if (xhr.status === 422) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors;
                        let errorList = '';
                        for (let field in errors) {
                            errorList += `• ${errors[field].join(', ')}\n`;
                        }
                        errorMessage = 'Validasi data gagal';
                        errorDetails = errorList;
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops!',
                        html: `
                            <div>
                                <p class="mb-1"><strong>Pesan:</strong> ${errorMessage}</p>
                                ${errorDetails ? `<pre class="text-start small mt-2">${errorDetails}</pre>` : ''}
                            </div>
                        `,
                        confirmButtonText: 'Coba Lagi',
                        confirmButtonColor: '#dc3545',
                        showClass: {
                            popup: 'animate__animated animate__shakeX'
                        }
                    });
                    
                    // Highlight validation errors
                    if (xhr.status === 422 && xhr.responseJSON.errors) {
                        Object.keys(xhr.responseJSON.errors).forEach(field => {
                            $(`[name="${field}"]`).addClass('is-invalid');
                        });
                    }
                }
            });
        }

        // Add session stats display (for debugging)
        function showSessionStats() {
            $.get('/api/session-stats')
                .done(function(response) {
                    console.log('Session Statistics:', response);
                })
                .fail(function() {
                    console.log('Could not retrieve session stats');
                });
        }

        // Auto-check session stats every 30 seconds (optional)
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            setInterval(showSessionStats, 30000);
        }

        // Real-time validation feedback
        $('input[required], select[required]').on('blur', function() {
            const field = $(this);
            if (!field.val()) {
                field.addClass('is-invalid');
            } else {
                field.removeClass('is-invalid').addClass('is-valid');
            }
        });

        $('input[required], select[required]').on('input change', function() {
            const field = $(this);
            if (field.val()) {
                field.removeClass('is-invalid').addClass('is-valid');
            }
        });
    });
</script>
@endpush