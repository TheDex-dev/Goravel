<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EscortApi;
use App\Http\Controllers\Api\EscortGoApiController;
use App\Http\Controllers\Api\QrCodeGoController;
use App\Http\Controllers\Api\AuthApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Sanctum authentication routes
Route::get('/sanctum/csrf-cookie', [AuthApiController::class, 'csrfToken']);
Route::post('/auth/login', [AuthApiController::class, 'login']);
Route::post('/auth/logout', [AuthApiController::class, 'logout']);
Route::get('/auth/check', [AuthApiController::class, 'check']);
Route::get('/auth/user', [AuthApiController::class, 'user']);
Route::get('/auth/sanctum', [AuthApiController::class, 'sanctum']);

// Authentication required routes
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// =============================================================================
// GO API ROUTES (Primary - Migrated to Go)
// =============================================================================

// Public API routes - accessible for escort form submissions (via Go API)
Route::post('/escort', [EscortGoApiController::class, 'store'])->name('api.go.escort.store');

// Session stats endpoint (public for monitoring) - Go API + Laravel session combination
Route::get('/session-stats', [EscortGoApiController::class, 'getSessionStats'])->name('api.go.session-stats');

// QR Code generation endpoints (public for easier access) - via Go API
Route::get('/qr-code/form', [QrCodeGoController::class, 'generateFormQrCode'])->name('api.go.qr-code.form.get');
Route::post('/qr-code/form', [QrCodeGoController::class, 'generateFormQrCodeJson'])->name('api.go.qr-code.form.post');

// Protected API routes - require authentication (IGD Staff only) - via Go API
Route::middleware('auth:sanctum')->group(function () {
    // Core CRUD operations via Go API
    Route::get('/escort', [EscortGoApiController::class, 'index'])->name('api.go.escort.index');
    Route::get('/escort/{id}', [EscortGoApiController::class, 'show'])->name('api.go.escort.show');
    Route::put('/escort/{id}', [EscortGoApiController::class, 'update'])->name('api.go.escort.update');
    Route::patch('/escort/{id}', [EscortGoApiController::class, 'update'])->name('api.go.escort.patch');
    Route::delete('/escort/{id}', [EscortGoApiController::class, 'destroy'])->name('api.go.escort.destroy');
    
    // Status management endpoint via Go API
    Route::patch('/escort/{id}/status', [EscortGoApiController::class, 'updateStatus'])->name('api.go.escort.status');
    
    // Base64 image endpoints via Go API
    Route::get('/escort/{id}/image/base64', [EscortGoApiController::class, 'getImageBase64'])->name('api.go.escort.image.get');
    Route::post('/escort/{id}/image/base64', [EscortGoApiController::class, 'uploadImageBase64'])->name('api.go.escort.image.post');
    
    // Dashboard stats via Go API
    Route::get('/dashboard/stats', [EscortGoApiController::class, 'getDashboardStats'])->name('api.go.dashboard.stats');
});

// =============================================================================
// LEGACY LARAVEL ROUTES (Fallback - Original Implementation)
// =============================================================================
// These routes are maintained for backward compatibility and fallback purposes
// They use the original Laravel controllers and database

// Legacy public routes
Route::prefix('legacy')->group(function () {
    Route::post('/escort', [EscortApi::class, 'store'])->name('api.legacy.escort.store');
    Route::get('/session-stats', [EscortApi::class, 'getSessionStats'])->name('api.legacy.session-stats');
    Route::get('/qr-code/form', [\App\Http\Controllers\EscortDataController::class, 'generateFormQrCode'])->name('api.legacy.qr-code.form');
});

// Legacy protected routes
Route::middleware('auth:sanctum')->prefix('legacy')->group(function () {
    Route::apiResource('escort', EscortApi::class)->except(['store']);
    Route::patch('/escort/{escort}/status', [EscortApi::class, 'updateStatus'])->name('api.legacy.escort.status');
    Route::get('/escort/{escort}/image/base64', [EscortApi::class, 'getImageBase64'])->name('api.legacy.escort.image.get');
    Route::post('/escort/{escort}/image/base64', [EscortApi::class, 'uploadImageBase64'])->name('api.legacy.escort.image.post');
    Route::get('/dashboard/stats', [EscortApi::class, 'getDashboardStats'])->name('api.legacy.dashboard.stats');
});
