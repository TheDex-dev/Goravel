<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EscortModel;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use App\Services\CsvExportService;
use App\Exports\EscortExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class EscortDataController extends Controller
{
    /**
     * Display the form for creating new escort data
     */
    public function index()
    {
        // Store form access in session for tracking
        Session::put('form_accessed_at', now());
        Session::put('form_user_ip', request()->ip());
        
        // Get flash messages from session
        $successMessage = Session::get('escort_success');
        $errorMessage = Session::get('escort_error');
        
        return view('form.index', compact('successMessage', 'errorMessage'));
    }
    
    /**
     * Display the dashboard with escort data and statistics
     */
    public function dashboard(Request $request)
    {
        // Store dashboard access in session with enhanced filter tracking
        Session::put('dashboard_accessed_at', now());
        Session::put('dashboard_filters', $request->only([
            'search', 'kategori', 'jenis_kelamin', 'status', 
            'today_only', 'week_only', 'month_only', 'date_specific'
        ]));
        
        $query = EscortModel::query();
        
        // Handle date filters with session persistence (mutually exclusive)
        if ($request->has('today_only') && $request->today_only == '1') {
            $query->whereDate('created_at', today());
            Session::put('last_date_filter', ['type' => 'today_only', 'value' => true]);
        } elseif ($request->has('week_only') && $request->week_only == '1') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            Session::put('last_date_filter', ['type' => 'week_only', 'value' => true]);
        } elseif ($request->has('month_only') && $request->month_only == '1') {
            $query->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
            Session::put('last_date_filter', ['type' => 'month_only', 'value' => true]);
        } elseif ($request->has('date_specific') && $request->date_specific != '') {
            $specificDate = $request->date_specific;
            // Validate date format
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $specificDate)) {
                $query->whereDate('created_at', $specificDate);
                Session::put('last_date_filter', ['type' => 'date_specific', 'value' => $specificDate]);
            }
        } else {
            // Clear date filter session if no date filter is active
            Session::forget('last_date_filter');
            Session::forget('last_today_only_filter'); // Legacy cleanup
        }
        
        // Handle search functionality with session persistence
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            Session::put('last_search', $search);
            
            $query->where(function($q) use ($search) {
                $q->where('nama_pengantar', 'like', "%{$search}%")
                  ->orWhere('nama_pasien', 'like', "%{$search}%")
                  ->orWhere('nomor_hp', 'like', "%{$search}%")
                  ->orWhere('plat_nomor', 'like', "%{$search}%")
                  ->orWhere('kategori_pengantar', 'like', "%{$search}%");
            });
        } elseif (!$request->has('search') && Session::has('last_search')) {
            // Restore previous search from session if no new search
            $search = Session::get('last_search');
            $query->where(function($q) use ($search) {
                $q->where('nama_pengantar', 'like', "%{$search}%")
                  ->orWhere('nama_pasien', 'like', "%{$search}%")
                  ->orWhere('nomor_hp', 'like', "%{$search}%")
                  ->orWhere('plat_nomor', 'like', "%{$search}%")
                  ->orWhere('kategori_pengantar', 'like', "%{$search}%");
            });
        }
        
        // Handle filter by category with session persistence
        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori_pengantar', $request->kategori);
            Session::put('last_kategori_filter', $request->kategori);
        }
        
        // Handle filter by gender with session persistence
        if ($request->has('jenis_kelamin') && $request->jenis_kelamin != '') {
            $query->where('jenis_kelamin', $request->jenis_kelamin);
            Session::put('last_gender_filter', $request->jenis_kelamin);
        }
        
        // Handle filter by status with session persistence
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
            Session::put('last_status_filter', $request->status);
        }
        
        // Order by latest first
        $query->orderBy('created_at', 'desc');
        
        // Handle view_all parameter with safety limit
        $isViewAll = $request->has('view_all') && $request->view_all == 'true';
        $recordCount = 0;
        $isLimited = false;
        
        if ($isViewAll) {
            // Safety limit to prevent performance issues
            $totalRecords = $query->count();
            $safetyLimit = 5000;
            
            if ($totalRecords > $safetyLimit) {
                $escorts = $query->limit($safetyLimit)->get();
                $recordCount = $safetyLimit;
                $isLimited = true;
                
                // Log when safety limit is applied
                Log::info('View all data limited to safety threshold', [
                    'total_records' => $totalRecords,
                    'safety_limit' => $safetyLimit,
                    'ip' => $request->ip(),
                    'filters' => $request->only([
                        'search', 'kategori', 'jenis_kelamin', 'status', 
                        'today_only', 'week_only', 'month_only', 'date_specific'
                    ])
                ]);
            } else {
                $escorts = $query->get();
                $recordCount = $totalRecords;
                $isLimited = false;
            }
            
            // Store view_all request in session for tracking
            Session::put('last_view_all_request', [
                'timestamp' => now(),
                'record_count' => $recordCount,
                'is_limited' => $isLimited,
                'total_available' => $totalRecords,
                'filters' => $request->only([
                    'search', 'kategori', 'jenis_kelamin', 'status', 
                    'today_only', 'week_only', 'month_only', 'date_specific'
                ])
            ]);
            
        } else {
            // Get page size from session or default
            $pageSize = Session::get('dashboard_page_size', 15);
            $escorts = $query->paginate($pageSize)->appends($request->query());
        }
        
        // Get cached or fresh statistics
        $stats = Cache::remember('escort_stats', 300, function() {
            return [
                'total' => EscortModel::count(),
                'today' => EscortModel::whereDate('created_at', today())->count(),
                'this_week' => EscortModel::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'this_month' => EscortModel::whereMonth('created_at', now()->month)->count(),
                'pending' => EscortModel::where('status', 'pending')->count(),
                'verified' => EscortModel::where('status', 'verified')->count(),
                'rejected' => EscortModel::where('status', 'rejected')->count(),
            ];
        });
        
        // Handle AJAX requests with session data
        if ($request->ajax()) {
            // Check if this is a preview request for download functionality
            if ($request->has('preview') && $request->preview == 'true') {
                return $this->getDownloadPreviewData($request);
            }
            
            // Store AJAX request in session for debugging
            Session::put('last_ajax_request', [
                'timestamp' => now(),
                'filters' => $request->all(),
                'results_count' => $isViewAll ? $recordCount : $escorts->count(),
                'is_view_all' => $isViewAll
            ]);
            
            $response = [
                'status' => 'success',
                'html' => view('partials.escort-table', compact('escorts'))->render(),
                'stats' => $stats,
                'session_id' => Session::getId(),
                'filters_applied' => Session::get('dashboard_filters', [])
            ];
            
            if ($isViewAll) {
                // For view_all requests, don't include pagination
                $response['pagination'] = '';
                $response['record_count'] = $recordCount;
                $response['is_limited'] = $isLimited;
                $response['view_all'] = true;
            } else {
                // For regular paginated requests
                $response['pagination'] = $escorts->links()->render();
            }
            
            return response()->json($response);
        }
        
        // Get recent form submissions from session
        $recentSubmissions = Session::get('recent_submissions', []);
        
        return view('dashboard', compact('escorts', 'stats', 'recentSubmissions'));
    }
    
    /**
     * Get preview data for download functionality
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function getDownloadPreviewData(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'kategori' => 'nullable|in:Polisi,Ambulans,Perorangan',
                'status' => 'nullable|in:pending,verified,rejected',
                'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
                'search' => 'nullable|string|max:255'
            ]);

            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            
            // Build query with filters
            $query = EscortModel::whereBetween('created_at', [$startDate, $endDate]);
            
            // Apply additional filters
            if ($request->filled('kategori')) {
                $query->where('kategori_pengantar', $request->kategori);
            }
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('jenis_kelamin')) {
                $query->where('jenis_kelamin', $request->jenis_kelamin);
            }
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nama_pengantar', 'like', "%{$search}%")
                      ->orWhere('nama_pasien', 'like', "%{$search}%")
                      ->orWhere('nomor_hp', 'like', "%{$search}%")
                      ->orWhere('plat_nomor', 'like', "%{$search}%")
                      ->orWhere('kategori_pengantar', 'like', "%{$search}%");
                });
            }
            
            // Get statistics for the filtered data
            $stats = [
                'total' => $query->count(),
                'verified' => (clone $query)->where('status', 'verified')->count(),
                'pending' => (clone $query)->where('status', 'pending')->count(),
                'rejected' => (clone $query)->where('status', 'rejected')->count(),
            ];
            
            // Store preview request in session for tracking
            Session::put('last_download_preview', [
                'timestamp' => now(),
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'filters' => $request->only(['kategori', 'status', 'jenis_kelamin', 'search']),
                'stats' => $stats,
                'ip' => $request->ip()
            ]);
            
            // Log preview request
            Log::info('Download preview requested', [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'filters' => $request->only(['kategori', 'status', 'jenis_kelamin', 'search']),
                'stats' => $stats,
                'ip' => $request->ip(),
                'user' => auth()->user()->name ?? 'Unknown'
            ]);
            
            return response()->json([
                'status' => 'success',
                'stats' => $stats,
                'period' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                    'days' => $startDate->diffInDays($endDate) + 1
                ],
                'filters' => $request->only(['kategori', 'status', 'jenis_kelamin', 'search']),
                'message' => 'Preview data berhasil dimuat'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Download preview failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memuat preview data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Store new escort data with enhanced session handling
     */
    public function store(Request $request)
    {
        // Start session tracking for this submission
        $submissionId = uniqid('sub_');
        Session::put("submission_{$submissionId}_started", now());
        Session::put("submission_{$submissionId}_ip", $request->ip());
        Session::put("submission_{$submissionId}_user_agent", $request->userAgent());
        
        try {
            // Enhanced validation with custom messages - support both file upload and base64
            $validatedData = $request->validate([
                'kategori_pengantar' => 'required|in:Polisi,Ambulans,Perorangan',
                'nama_pengantar' => 'required|string|max:255|min:3',
                'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
                'nomor_hp' => 'required|string|max:20|min:10|regex:/^[0-9+\-\s]+$/',
                'plat_nomor' => 'required|string|max:20|min:3',
                'nama_pasien' => 'required|string|max:255|min:3',
                'foto_pengantar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'foto_pengantar_base64' => 'nullable|string',
                'foto_pengantar_info' => 'nullable|array'
            ], [
                'nama_pengantar.required' => 'Nama pengantar wajib diisi.',
                'nama_pengantar.min' => 'Nama pengantar minimal 3 karakter.',
                'nomor_hp.required' => 'Nomor HP wajib diisi.',
                'nomor_hp.regex' => 'Format nomor HP tidak valid.',
                'nomor_hp.min' => 'Nomor HP minimal 10 digit.',
                'plat_nomor.required' => 'Plat nomor wajib diisi.',
                'plat_nomor.min' => 'Plat nomor minimal 3 karakter.',
                'nama_pasien.required' => 'Nama pasien wajib diisi.',
                'nama_pasien.min' => 'Nama pasien minimal 3 karakter.',
                'foto_pengantar.image' => 'File harus berupa gambar.',
                'foto_pengantar.max' => 'Ukuran foto maksimal 2MB.',
            ]);
            
            // Handle image upload - support both traditional file upload and base64
            $imageProcessed = false;
            
            // Priority 1: Handle base64 image if provided
            if (!empty($validatedData['foto_pengantar_base64'])) {
                $base64Image = $validatedData['foto_pengantar_base64'];
                $imageInfo = $validatedData['foto_pengantar_info'] ?? [];
                
                // Validate and process base64 image
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                    $imageExtension = strtolower($type[1]);
                    
                    if (!in_array($imageExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
                        throw new \Exception('Format gambar tidak didukung. Gunakan JPG, PNG, atau GIF.');
                    }
                    
                    $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
                    $imageData = base64_decode($base64Image);
                    
                    if ($imageData === false) {
                        throw new \Exception('Gagal memproses data gambar.');
                    }
                    
                    $imageSize = strlen($imageData);
                    if ($imageSize > 2 * 1024 * 1024) {
                        throw new \Exception('Ukuran gambar melebihi batas maksimal 2MB.');
                    }
                    
                    $originalName = $imageInfo['name'] ?? 'uploaded_image.' . $imageExtension;
                    $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $originalName);
                    
                    $storagePath = 'public/uploads/' . $filename;
                    if (!Storage::put($storagePath, $imageData)) {
                        throw new \Exception('Gagal menyimpan gambar ke storage.');
                    }
                    
                    Session::put("submission_{$submissionId}_file", [
                        'original_name' => $originalName,
                        'stored_name' => $filename,
                        'size' => $imageSize,
                        'mime_type' => $imageInfo['type'] ?? 'image/' . $imageExtension,
                        'base64_length' => strlen($validatedData['foto_pengantar_base64']),
                        'extension' => $imageExtension,
                        'processing_method' => 'base64'
                    ]);
                    
                    $validatedData['foto_pengantar'] = 'uploads/' . $filename;
                    $imageProcessed = true;
                }
                
                // Clean up base64 fields from validated data
                unset($validatedData['foto_pengantar_base64']);
                unset($validatedData['foto_pengantar_info']);
            }
            
            // Priority 2: Handle traditional file upload if no base64 was processed
            if (!$imageProcessed && $request->hasFile('foto_pengantar')) {
                $file = $request->file('foto_pengantar');
                $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                
                Session::put("submission_{$submissionId}_file", [
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'stored_name' => $filename,
                    'processing_method' => 'file_upload'
                ]);
                
                $file->storeAs('public/uploads', $filename);
                $validatedData['foto_pengantar'] = 'uploads/' . $filename;
                $imageProcessed = true;
            }
            
            // Require image upload (either method)
            if (!$imageProcessed) {
                throw new \Exception('Foto pengantar wajib diisi.');
            }
            
            // Add submission tracking data
            $validatedData['submission_id'] = $submissionId;
            $validatedData['submitted_from_ip'] = $request->ip();
            $validatedData['status'] = 'pending'; // Set default status
            
            // Create the escort record
            $escort = EscortModel::create($validatedData);
            
            // Store success in session
            Session::put("submission_{$submissionId}_completed", now());
            Session::put("submission_{$submissionId}_escort_id", $escort->id);
            
            // Add to recent submissions list
            $recentSubmissions = Session::get('recent_submissions', []);
            array_unshift($recentSubmissions, [
                'id' => $escort->id,
                'nama_pengantar' => $escort->nama_pengantar,
                'nama_pasien' => $escort->nama_pasien,
                'kategori' => $escort->kategori_pengantar,
                'submitted_at' => now(),
                'submission_id' => $submissionId
            ]);
            
            // Keep only last 10 submissions in session
            $recentSubmissions = array_slice($recentSubmissions, 0, 10);
            Session::put('recent_submissions', $recentSubmissions);
            
            // Flash success message
            Session::flash('escort_success', 'Data escort berhasil ditambahkan! ID: ' . $escort->id);
            
            // Clear cache
            Cache::forget('escort_stats');
            
            // Log successful submission
            Log::info('Escort data submitted successfully', [
                'submission_id' => $submissionId,
                'escort_id' => $escort->id,
                'ip' => $request->ip(),
                'category' => $escort->kategori_pengantar
            ]);
            
            // Return appropriate response based on request type
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data escort berhasil ditambahkan!',
                    'data' => $escort,
                    'submission_id' => $submissionId,
                    'redirect' => route('form.index')
                ], 201);
            }
            
            return redirect()->route('form.index')->with('success', 'Data escort berhasil ditambahkan!');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Store validation errors in session
            Session::put("submission_{$submissionId}_failed", now());
            Session::put("submission_{$submissionId}_errors", $e->errors());
            
            Session::flash('escort_error', 'Validasi gagal: ' . implode(', ', array_flatten($e->errors())));
            
            Log::warning('Escort submission validation failed', [
                'submission_id' => $submissionId,
                'errors' => $e->errors(),
                'ip' => $request->ip()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors(),
                    'submission_id' => $submissionId
                ], 422);
            }
            
            return redirect()->back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            // Store general error in session
            Session::put("submission_{$submissionId}_error", now());
            Session::put("submission_{$submissionId}_error_message", $e->getMessage());
            
            Session::flash('escort_error', 'Gagal menambahkan data: ' . $e->getMessage());
            
            Log::error('Escort submission failed', [
                'submission_id' => $submissionId,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal menambahkan data escort',
                    'error' => $e->getMessage(),
                    'submission_id' => $submissionId
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Gagal menambahkan data: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Get submission details from session
     */
    public function getSubmissionDetails($submissionId)
    {
        $details = [
            'started' => Session::get("submission_{$submissionId}_started"),
            'completed' => Session::get("submission_{$submissionId}_completed"),
            'failed' => Session::get("submission_{$submissionId}_failed"),
            'ip' => Session::get("submission_{$submissionId}_ip"),
            'user_agent' => Session::get("submission_{$submissionId}_user_agent"),
            'file_info' => Session::get("submission_{$submissionId}_file"),
            'escort_id' => Session::get("submission_{$submissionId}_escort_id"),
            'errors' => Session::get("submission_{$submissionId}_errors"),
        ];
        
        return response()->json($details);
    }
    
    /**
     * Clear old session data (can be called via scheduled job)
     */
    public function clearOldSessionData()
    {
        $sessionKeys = array_keys(Session::all());
        $cleared = 0;
        
        foreach ($sessionKeys as $key) {
            if (strpos($key, 'submission_') === 0) {
                $timestamp = Session::get($key);
                if ($timestamp && now()->diffInHours($timestamp) > 24) {
                    Session::forget($key);
                    $cleared++;
                }
            }
        }
        
        return response()->json(['cleared' => $cleared]);
    }
    
    /**
     * Update escort status
     */
    public function updateStatus(Request $request, EscortModel $escort)
    {
        // Validate the request
        $request->validate([
            'status' => 'required|in:pending,verified,rejected'
        ]);
        
        $oldStatus = $escort->status;
        $newStatus = $request->status;
        
        // Update the escort status
        $escort->update([
            'status' => $newStatus
        ]);
        
        // Clear cache
        Cache::forget('escort_stats');
        
        // Log the status change
        Log::info('Escort status updated', [
            'escort_id' => $escort->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'updated_by' => auth()->user()->name ?? 'Unknown',
            'ip' => $request->ip()
        ]);
        
        // Store status change in session for tracking
        Session::put("status_change_{$escort->id}", [
            'changed_at' => now(),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => auth()->user()->name ?? 'Unknown'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui',
            'status' => $escort->status,
            'status_display' => $escort->getStatusDisplayName(),
            'badge_class' => $escort->getStatusBadgeClass()
        ]);
    }
    
    /**
     * Generate QR code for form submission URL
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateFormQrCode(Request $request)
    {
        try {
            // Track QR code generation in session
            Session::put('qr_generated_at', now());
            Session::put('qr_generated_ip', $request->ip());
            Session::increment('qr_generation_count');
            
            // Get the base URL for the form
            $baseUrl = config('app.url');
            $formUrl = $baseUrl . '/form';
            
            // Try different rendering backends based on availability
            try {
                // Try SVG backend first (most compatible)
                $renderer = new ImageRenderer(
                    new RendererStyle(400),
                    new SvgImageBackEnd()
                );
                $writer = new Writer($renderer);
                $qrCode = $writer->writeString($formUrl);
                $contentType = 'image/svg+xml';
                $filename = 'form-qrcode.svg';
                
            } catch (\Exception $svgError) {
                // If SVG fails, try with plain text output and create a simple HTML QR code
                Log::warning('SVG QR generation failed, falling back to simple text QR', [
                    'error' => $svgError->getMessage()
                ]);
                
                // Create a simple text-based QR code as fallback
                $qrCode = $this->generateSimpleQrCode($formUrl);
                $contentType = 'image/svg+xml';
                $filename = 'form-qrcode-fallback.svg';
            }
            
            // Log QR code generation
            Log::info('QR Code generated for form', [
                'url' => $formUrl,
                'ip' => $request->ip(),
                'timestamp' => now(),
                'content_type' => $contentType
            ]);
            
            // Return QR code
            return response($qrCode)
                ->header('Content-Type', $contentType)
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->header('Cache-Control', 'public, max-age=3600'); // Cache for 1 hour
                
        } catch (\Exception $e) {
            Log::error('QR Code generation failed', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return error response
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal generate QR code',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            // Return a simple error image as SVG
            $errorSvg = $this->generateErrorQrCode();
            return response($errorSvg)
                ->header('Content-Type', 'image/svg+xml')
                ->header('Content-Disposition', 'inline; filename="error-qrcode.svg"');
        }
    }
    
    /**
     * Download escort data as Excel file
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function downloadExcel(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'kategori' => 'nullable|in:Polisi,Ambulans,Perorangan',
            'status' => 'nullable|in:pending,verified,rejected',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'search' => 'nullable|string|max:255'
        ]);

        try {
            $filters = $request->only(['kategori', 'status', 'jenis_kelamin', 'search']);
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            
            // Generate filename with date range
            $filename = 'Data_Escort_IGD_' . 
                       Carbon::parse($startDate)->format('d-m-Y') . '_sampai_' . 
                       Carbon::parse($endDate)->format('d-m-Y') . '_' . 
                       now()->format('His') . '.xlsx';

            // Log download request
            Log::info('Excel export requested', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'filters' => $filters,
                'filename' => $filename,
                'ip' => $request->ip(),
                'user' => auth()->user()->name ?? 'Unknown'
            ]);

            // Track download in session
            Session::put('last_excel_download', [
                'timestamp' => now(),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'filters' => $filters,
                'filename' => $filename
            ]);

            // Generate Excel file using EscortExport
            return Excel::download(
                new EscortExport($startDate, $endDate, $filters), 
                $filename
            );

        } catch (\Exception $e) {
            Log::error('Excel export failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return back()->with('error', 'Gagal mengunduh file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Download escort data as CSV file
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function downloadCsv(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'kategori' => 'nullable|in:Polisi,Ambulans,Perorangan',
            'status' => 'nullable|in:pending,verified,rejected',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'search' => 'nullable|string|max:255'
        ]);

        try {
            $filters = $request->only(['kategori', 'status', 'jenis_kelamin', 'search']);
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            
            // Generate filename with date range
            $filename = 'Data_Escort_IGD_' . 
                       Carbon::parse($startDate)->format('d-m-Y') . '_sampai_' . 
                       Carbon::parse($endDate)->format('d-m-Y') . '_' . 
                       now()->format('His') . '.csv';

            // Log download request
            Log::info('CSV export requested', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'filters' => $filters,
                'filename' => $filename,
                'ip' => $request->ip(),
                'user' => auth()->user()->name ?? 'Unknown'
            ]);

            // Track download in session
            Session::put('last_csv_download', [
                'timestamp' => now(),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'filters' => $filters,
                'filename' => $filename
            ]);

            // Generate CSV content using service
            $csvContent = CsvExportService::generateCsv($startDate, $endDate, $filters);

            return response($csvContent)
                ->header('Content-Type', 'text/csv; charset=utf-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            Log::error('CSV export failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return back()->with('error', 'Gagal mengunduh file CSV: ' . $e->getMessage());
        }
    }

    /**
     * Generate a simple QR code as fallback when main library fails
     * 
     * @param string $url
     * @return string
     */
    private function generateSimpleQrCode($url)
    {
        // Create a simple SVG with the URL as text and a basic QR-like pattern
        $encodedUrl = htmlspecialchars($url);
        return '<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg">
            <rect width="400" height="400" fill="white"/>
            <rect x="20" y="20" width="360" height="360" fill="none" stroke="black" stroke-width="2"/>
            <text x="200" y="200" text-anchor="middle" font-family="Arial" font-size="12" fill="black">QR Code</text>
            <text x="200" y="220" text-anchor="middle" font-family="Arial" font-size="8" fill="black">Scan untuk akses:</text>
            <text x="200" y="240" text-anchor="middle" font-family="Arial" font-size="10" fill="blue" text-decoration="underline">' . $encodedUrl . '</text>
            <rect x="50" y="50" width="60" height="60" fill="black"/>
            <rect x="290" y="50" width="60" height="60" fill="black"/>
            <rect x="50" y="290" width="60" height="60" fill="black"/>
            <rect x="60" y="60" width="40" height="40" fill="white"/>
            <rect x="300" y="60" width="40" height="40" fill="white"/>
            <rect x="60" y="300" width="40" height="40" fill="white"/>
        </svg>';
    }
    
    /**
     * Generate an error QR code when generation fails
     * 
     * @return string
     */
    private function generateErrorQrCode()
    {
        return '<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg">
            <rect width="400" height="400" fill="#ffebee"/>
            <rect x="20" y="20" width="360" height="360" fill="none" stroke="#f44336" stroke-width="2"/>
            <text x="200" y="180" text-anchor="middle" font-family="Arial" font-size="16" fill="#f44336">QR Code Error</text>
            <text x="200" y="200" text-anchor="middle" font-family="Arial" font-size="12" fill="#666">Gagal generate QR code</text>
            <text x="200" y="220" text-anchor="middle" font-family="Arial" font-size="10" fill="#666">Silakan akses manual:</text>
            <text x="200" y="240" text-anchor="middle" font-family="Arial" font-size="10" fill="#1976d2">' . config('app.url') . '/form</text>
        </svg>';
    }
}