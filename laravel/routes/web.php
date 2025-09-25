<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EscortDataController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public Routes (accessible without authentication)
Route::get('/form', [EscortDataController::class, 'index'])->name('form.index');
Route::post('/form/submit', [EscortDataController::class, 'store'])->name('form.store');

// QR Code generation route
Route::get('/qr-code/form', [EscortDataController::class, 'generateFormQrCode'])->name('qr.form');

// QR Code demo/test page
Route::get('/qr-demo', function () {
    $qrUrl = route('qr.form');
    $formUrl = route('form.index');
    return view('qr-demo', compact('qrUrl', 'formUrl'));
})->name('qr.demo');

// Excel implementation test page
Route::get('/excel-test', function () {
    return view('excel-test');
})->name('excel.test');

// Session and submission tracking routes (public for form debugging)
Route::get('/submission/{submissionId}', [EscortDataController::class, 'getSubmissionDetails'])->name('submission.details');

// Protected Routes (require authentication - IGD Staff only)
Route::middleware('auth')->group(function () {
    Route::get('/', [EscortDataController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [EscortDataController::class, 'dashboard'])->name('dashboard');
    
    // Escort status management
    Route::patch('/escorts/{escort}/status', [EscortDataController::class, 'updateStatus'])->name('escorts.update-status');
    
    // Data export/download routes
    Route::post('/dashboard/download/csv', [EscortDataController::class, 'downloadCsv'])->name('dashboard.download.csv');
    Route::post('/dashboard/download/excel', [EscortDataController::class, 'downloadExcel'])->name('dashboard.download.excel');
    
    // Admin utilities
    Route::post('/admin/clear-session-data', [EscortDataController::class, 'clearOldSessionData'])->name('admin.clear-session');
});

// Go API Connection Test Route (for development/testing)
Route::get('/test-go-connection', function () {
    try {
        $goApiService = app(\App\Services\GoApiService::class);
        
        // Test the connection
        $healthResult = $goApiService->healthCheck();
        $statsResult = $goApiService->getDashboardStats();
        
        return response()->json([
            'success' => true,
            'message' => 'Laravel to Go API connection successful!',
            'timestamp' => now()->toISOString(),
            'health_check' => $healthResult,
            'dashboard_stats' => $statsResult,
            'go_api_url' => $goApiService->getBaseUrl()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Laravel to Go API connection failed!',
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString(),
        ], 500);
    }
})->name('test.go.connection');

// Redirect root to dashboard if authenticated, otherwise to login
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');