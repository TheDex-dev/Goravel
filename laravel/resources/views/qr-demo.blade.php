@extends('layout.app')

@section('title', 'QR Code Demo')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <!-- QR Code Card -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-gradient-primary text-white py-4 border-0">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 rounded-circle bg-white bg-opacity-25 p-3">
                            <i class="fas fa-qrcode fa-2x text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0 fw-bold">QR Code untuk Form Submission</h4>
                            <p class="mb-0 text-white-50">Scan untuk mengakses form pendaftaran pengantar</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-6 text-center mb-4 mb-md-0">
                            <div class="bg-light rounded-4 p-4 mb-3">
                                <img src="{{ $qrUrl }}" alt="QR Code for Form Submission" class="img-fluid" style="max-width: 300px;">
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <a href="{{ $qrUrl }}" class="btn btn-primary px-4" download="form-qrcode.svg">
                                    <i class="fas fa-download me-2"></i> Download QR
                                </a>
                                <a href="{{ $formUrl }}" class="btn btn-success px-4">
                                    <i class="fas fa-external-link-alt me-2"></i> Buka Form
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light border-0 rounded-4">
                                <div class="card-body p-4">
                                    <h5 class="fw-bold mb-3">
                                        <i class="fas fa-link text-primary me-2"></i>URL yang Dikodekan
                                    </h5>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" value="{{ $formUrl }}" readonly>
                                        <a href="{{ $formUrl }}" class="btn btn-outline-primary" target="_blank">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Usage Instructions Card -->
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-gradient-info text-white py-4 border-0">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 rounded-circle bg-white bg-opacity-25 p-3">
                            <i class="fas fa-info-circle fa-2x text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0 fw-bold">Panduan Penggunaan</h4>
                            <p class="mb-0 text-white-50">Informasi teknis dan cara penggunaan QR code</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card bg-light border-0 rounded-4 h-100">
                                <div class="card-body p-4">
                                    <h5 class="fw-bold mb-3">
                                        <i class="fas fa-code text-info me-2"></i>Informasi Teknis
                                    </h5>
                                    <ul class="list-group list-group-flush bg-transparent">
                                        <li class="list-group-item bg-transparent px-0 py-2">
                                            <small class="text-muted d-block">Public URL</small>
                                            <code class="text-dark">{{ $qrUrl }}</code>
                                        </li>
                                        <li class="list-group-item bg-transparent px-0 py-2">
                                            <small class="text-muted d-block">API Endpoint</small>
                                            <code class="text-dark">{{ url('/api/qr-code/form') }}</code>
                                        </li>
                                        <li class="list-group-item bg-transparent px-0 py-2">
                                            <small class="text-muted d-block">Target Form</small>
                                            <code class="text-dark">{{ $formUrl }}</code>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light border-0 rounded-4 h-100">
                                <div class="card-body p-4">
                                    <h5 class="fw-bold mb-3">
                                        <i class="fas fa-clipboard-list text-info me-2"></i>Cara Penggunaan
                                    </h5>
                                    <ol class="ps-3 mb-0">
                                        <li class="mb-2">Akses URL QR code untuk mendapatkan gambar QR</li>
                                        <li class="mb-2">Print atau tampilkan QR code di lokasi yang mudah diakses</li>
                                        <li class="mb-2">Pengguna dapat scan QR code dengan smartphone untuk langsung mengakses form</li>
                                        <li class="mb-0">Form submission akan diterima dan diproses secara normal</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
@endpush