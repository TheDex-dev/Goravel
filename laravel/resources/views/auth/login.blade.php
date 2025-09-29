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
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
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
