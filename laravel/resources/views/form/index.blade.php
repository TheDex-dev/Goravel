@extends('layout.app')

@section('title','Formulir Pengantar Pasien')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/form.css') }}">

@endpush

@section('content')
<div class="container-fluid">
    <button id="darkModeToggle" class="btn btn-outline-secondary theme-toggle-button">
        <i class="fas fa-moon"></i>
    </button>
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
                <br>
            </div>
            <div class="card-body">
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
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <input type="text" class="form-control" id="nama_pengantar" name="nama_pengantar" 
                                           placeholder="Masukkan nama lengkap" required>
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
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <input type="tel" class="form-control" id="nomor_hp" name="nomor_hp" 
                                           placeholder="Contoh: 08123456789" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="plat_nomor" class="form-label">
                                    <i class="fas fa-car"></i> Plat Nomor
                                </label>
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <i class="fas fa-car"></i>
                                    </div>
                                    <input type="text" class="form-control" id="plat_nomor" name="plat_nomor" 
                                           placeholder="Contoh: B 1234 ABC" required>
                                </div>
                            </div>
                    <div class="form-group">
                        <label for="nama_pasien" class="form-label">
                            <i class="fas fa-user-injured"></i> Nama Pasien
                        </label>
                        <div class="input-group">
                            <div class="input-group-text">
                                <i class="fas fa-user-injured"></i>
                            </div>
                            <input type="text" class="form-control" id="nama_pasien" name="nama_pasien" 
                                   placeholder="Masukkan nama lengkap pasien" required>
                        </div>
                    </div>

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

                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span class="btn-text">
                            <i class="fas fa-paper-plane"></i> Kirim Data
                        </span>
                        <span class="loading-spinner">
                            <i class="fas fa-spinner fa-spin"></i> Memproses...
                        </span>
                    </button>
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
                        
                        // Focus first input for next entry
                        $('#kategori_pengantar').focus();
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
                                <p>${errorMessage}</p>
                                ${errorDetails ? `<div class="mt-2"><small>${errorDetails.replace(/\n/g, '<br>')}</small></div>` : ''}
                                ${xhr.responseJSON && xhr.responseJSON.submission_id ? 
                                    `<div class="mt-2"><small class="text-muted">ID Error: ${xhr.responseJSON.submission_id}</small></div>` : ''}
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
                field.css('border-color', '#dc3545');
            } else {
                field.css('border-color', '#28a745');
            }
        });

        $('input[required], select[required]').on('input change', function() {
            const field = $(this);
            if (field.val()) {
                field.css('border-color', '#28a745');
            }
        });
    });
</script>
@endpush