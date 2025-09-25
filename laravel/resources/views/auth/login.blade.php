@extends('layout.app')

{{-- =======================
    Judul Halaman
======================= --}}
@section('title', 'Login - Pendataan IGD')

{{-- =======================
    Tambahan Style Custom
======================= --}}
@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
    /* ===== Global Styling ===== */
    body {
        background: whitesmoke;
        min-height: 100vh;
        margin: 100px;
        padding: 0;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        overflow-x: hidden;
    }

    /* ===== Wrapper utama login page ===== */
    .login-page {
        position: absolute;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 100px;
    }
    
    /* ===== Container untuk form login ===== */
    .login-container {
        max-width: 420px;
        width: 90%;
        margin: 0 auto;
        position: relative;
    }

    /* ===== Card utama login ===== */
    .login-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 1rem;
        border: 1px solid #e3e6f0;
        box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.25);
        overflow: hidden;
        transform: translateY(-20px);
        animation: slideDown 0.6s ease-out forwards;
    }

    /* ===== Animasi card turun ===== */
    @keyframes slideDown {
        to {
            transform: translateY(0);
        }
    }

    /* ===== Header card ===== */
    .card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
        padding: 1.5rem 1.25rem;
        text-align: center;
    }
    .logo {
    height:150px;
    width:auto;
    margin-right:10px;
}

    /* ===== Body card ===== */
    .card-body {
        padding: 2rem 1.25rem;
    }

    /* ===== Title di atas form ===== */
    .login-title {
        color: #5a5c69;
        font-weight: 600;
        font-size: 1.5rem;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    /* ===== Subtitle kecil ===== */
    .login-subtitle {
        color: #858796;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        margin-bottom: 0;
    }

    /* ===== Spasi antar input ===== */
    .form-group {
        margin-bottom: 1.5rem;
    }

    /* ===== Input field umum ===== */
    .form-control {
        display: block;
        width: 100%;
        padding: 0.75rem;
        font-size: 0.875rem;
        font-weight: 400;
        line-height: 1.5;
        color: #6e707e;
        background-color: #fff;
        border: 1px solid #d1d3e2;
        border-radius: 0.35rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    /* ===== Efek fokus pada input ===== */
    .form-control:focus {
        color: #6e707e;
        background-color: #fff;
        border-color: #bac8f3;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    /* ===== Placeholder styling ===== */
    .form-control::placeholder {
        color: #858796;
        opacity: 1;
    }

    /* ===== Input group untuk ikon + input ===== */
    .input-group {
        display: flex;
        width: 100%;
    }

    /* ===== Ikon di input group ===== */
    .input-group-text {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        font-size: 0.875rem;
        color: #6e707e;
        background-color: #f8f9fc;
        border: 1px solid #d1d3e2;
        border-radius: 0.35rem;
    }

    /* ===== Styling button utama ===== */
    .btn-primary {
        color: #fff;
        background-color: #4e73df;
        border-color: #4e73df;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        border-radius: 0.35rem;
        font-weight: 600;
        width: 100%;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: 0.15s;
    }

    .btn-primary:hover {
        background-color: #2e59d9;
    }

    /* ===== Checkbox Remember Me ===== */
    .form-check {
        display: block;
        padding-left: 1.5rem;
    }

    /* ===== Spinner loading di tombol ===== */
    .loading-spinner {
        display: none;
    }

    .btn-primary.loading .btn-text {
        display: none;
    }

    .btn-primary.loading .loading-spinner {
        display: inline-block;
    }

    /* ===== Alert error (login gagal) ===== */
    .alert {
        padding: 0.75rem 1rem;
        border-radius: 0.35rem;
        font-size: 0.875rem;
    }

    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }

    /* ===== Info sistem di bawah form ===== */
    .system-info {
        text-align: center;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e3e6f0;
    }

    .system-info h6 {
        color: #5a5c69;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .system-info p {
        color: #858796;
        font-size: 0.75rem;
    }
</style>
@endpush

{{-- =======================
    Konten Utama (Form Login)
======================= --}}
@section('content')
<div class="login-page">
    <div class="login-container">
        <div class="login-card">

            {{-- Header Form --}}
            <div class="card-header border-left-primary">
                <img src="{{ asset('Rumah_Sakit_Indriati.webp') }}" alt="Logo" 
                class="logo">
                <h4 class="login-title">
                    <i class="fas fa-hospital"></i>
                    Pendataan IGD
                </h4>
                <p class="login-subtitle">Silakan masuk untuk melanjutkan</p>
            </div>
            
            <div class="card-body">

                {{-- Tampilkan Error Validasi --}}
                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Login gagal!</strong>
                        @foreach($errors->all() as $error)
                            <br>{{ $error }}
                        @endforeach
                    </div>
                @endif

                {{-- Tampilkan Error dari Session --}}
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Form Login --}}
                <form method="POST" action="{{ route('login.process') }}" id="loginForm">
                    @csrf
                    
                    {{-- Input Email --}}
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <div class="input-group">
                            <div class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   placeholder="Masukkan email Anda" 
                                   required 
                                   autofocus>
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Input Password --}}
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <div class="input-group">
                            <div class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Masukkan password Anda" 
                                   required>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Checkbox Remember Me --}}
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Ingat saya
                            </label>
                        </div>
                    </div>

                    {{-- Tombol Submit --}}
                    <button type="submit" class="btn btn-primary" id="loginBtn">
                        <span class="btn-text">
                            <i class="fas fa-sign-in-alt"></i> Masuk
                        </span>
                        <span class="loading-spinner">
                            <i class="fas fa-spinner fa-spin"></i> Memproses...
                        </span>
                    </button>
                </form>

                {{-- Informasi Sistem --}}
                <div class="system-info">
                    <h6><i class="fas fa-info-circle"></i> Informasi Sistem</h6>
                    <p>Sistem Pendataan Pengantar Pasien IGD<br>Rumah Sakit Indrianti - {{ date('Y') }}</p>
                    <p>Untuk bantuan teknis, hubungi administrator sistem</p>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

{{-- =======================
    Script Tambahan
======================= --}}
@push('scripts')
<script>
$(document).ready(function() {
    // === Handle submit form dengan efek loading ===
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        const submitBtn = $('#loginBtn');
        const form = this;

        // Tambahkan state loading ke button
        submitBtn.addClass('loading').prop('disabled', true);

        // Delay 0.5s sebelum submit agar spinner terlihat
        setTimeout(function() {
            form.submit();
        }, 500);
    });

    // === Validasi input real-time (kosong) ===
    $('input[required]').on('blur', function() {
        const field = $(this);
        const value = field.val().trim();
        if (!value) {
            field.addClass('is-invalid');
        } else {
            field.removeClass('is-invalid');
        }
    });

    // === Validasi email real-time ===
    $('#email').on('input', function() {
        const email = $(this).val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // === Fokus otomatis ke email jika belum ada autofocus ===
    if (!$('[autofocus]').length) {
        $('#email').focus();
    }
});
</script>
@endpush
