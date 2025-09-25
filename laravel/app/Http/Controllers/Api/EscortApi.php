<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EscortModel;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EscortApi extends Controller
{
    /**
     * Process base64 image using manual processing with Base64ImageDecoder as fallback
     *
     * @param string $base64Image
     * @param array $imageInfo
     * @param string $submissionId
     * @return array
     * @throws \Exception
     */
    private function processBase64Image($base64Image, $imageInfo = [], $submissionId = null)
    {
        try {
            // Ensure imageInfo is an array
            if (!is_array($imageInfo)) {
                $imageInfo = [];
            }
            
            // Validate base64 string
            if (empty($base64Image) || !is_string($base64Image)) {
                throw new \Exception('Base64 image data is required and must be a string.');
            }
            
            // Use manual base64 processing to avoid package issues
            $format = null;
            $imageData = null;
            $imageSize = 0;
            
            // Check if it's a data URL
            if (strpos($base64Image, 'data:image/') === 0) {
                // Extract format and data from data URL
                if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $base64Image, $matches)) {
                    $format = strtolower($matches[1]);
                    $base64Data = $matches[2];
                } else {
                    throw new \Exception('Format data URL tidak valid.');
                }
            } else {
                // Assume it's raw base64 data, try to detect format or default to PNG
                $base64Data = $base64Image;
                
                // Try to detect format from the first few bytes after decoding
                $testData = base64_decode(substr($base64Data, 0, 100));
                if ($testData !== false) {
                    if (substr($testData, 0, 8) === "\x89PNG\r\n\x1a\n") {
                        $format = 'png';
                    } elseif (substr($testData, 0, 3) === "\xFF\xD8\xFF") {
                        $format = 'jpeg';
                    } elseif (substr($testData, 0, 6) === "GIF87a" || substr($testData, 0, 6) === "GIF89a") {
                        $format = 'gif';
                    } else {
                        $format = 'png'; // Default fallback
                    }
                } else {
                    throw new \Exception('Data base64 tidak valid.');
                }
            }
            
            // Validate format
            if (!in_array($format, ['jpeg', 'jpg', 'png', 'gif'])) {
                throw new \Exception('Format gambar tidak didukung. Gunakan JPEG, PNG, atau GIF.');
            }
            
            // Decode base64 data
            $imageData = base64_decode($base64Data);
            if ($imageData === false) {
                throw new \Exception('Gagal mendecode data base64.');
            }
            
            $imageSize = strlen($imageData);
            
            // Check file size (2MB limit)
            if ($imageSize > 2097152) {
                throw new \Exception('Ukuran gambar terlalu besar. Maksimal 2MB.');
            }
            
            // Validate that it's actually an image by checking the header
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detectedMimeType = finfo_buffer($finfo, $imageData);
            finfo_close($finfo);
            
            if (!$detectedMimeType || strpos($detectedMimeType, 'image/') !== 0) {
                throw new \Exception('File yang diupload bukan gambar yang valid.');
            }
            
            // Generate unique filename
            $originalName = isset($imageInfo['name']) && is_string($imageInfo['name']) 
                ? $imageInfo['name'] 
                : 'uploaded_image.' . $format;
            $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $originalName);
            
            // Ensure the filename has correct extension
            $pathInfo = pathinfo($filename);
            if (!isset($pathInfo['extension']) || !in_array(strtolower($pathInfo['extension']), ['jpg', 'jpeg', 'png', 'gif'])) {
                $filename = $pathInfo['filename'] . '.' . $format;
            }
            
            // Save to storage
            $storagePath = 'public/uploads/' . $filename;
            if (!Storage::put($storagePath, $imageData)) {
                throw new \Exception('Gagal menyimpan gambar ke storage.');
            }
            
            // Store file info in session if submission ID is provided
            if ($submissionId) {
                Session::put("api_submission_{$submissionId}_file", [
                    'original_name' => $originalName,
                    'stored_name' => $filename,
                    'size' => $imageSize,
                    'mime_type' => isset($imageInfo['type']) && is_string($imageInfo['type']) 
                        ? $imageInfo['type'] 
                        : 'image/' . $format,
                    'base64_length' => strlen($base64Image),
                    'format' => $format,
                    'processed_with' => 'melihovv/base64-image-decoder'
                ]);
            }
            
            return [
                'success' => true,
                'file_path' => 'uploads/' . $filename,
                'file_info' => [
                    'original_name' => $originalName,
                    'stored_name' => $filename,
                    'size' => $imageSize,
                    'format' => $format,
                    'mime_type' => 'image/' . $format
                ]
            ];
            
        } catch (\Exception $e) {
            // Log detailed error information for debugging
            Log::error('Base64 image processing error', [
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'base64_length' => strlen($base64Image ?? ''),
                'image_info_type' => gettype($imageInfo),
                'image_info_content' => is_array($imageInfo) ? json_encode($imageInfo) : $imageInfo,
                'submission_id' => $submissionId
            ]);
            
            throw new \Exception('Gagal memproses gambar: ' . $e->getMessage());
        }
    }
    
    /**
     * Convert image file to base64 for API responses
     *
     * @param string $imagePath
     * @return array|null
     */
    private function convertImageToBase64($imagePath)
    {
        try {
            if (!$imagePath) {
                return null;
            }
            
            $fullPath = storage_path('app/public/' . $imagePath);
            
            if (!file_exists($fullPath)) {
                return null;
            }
            
            $imageData = file_get_contents($fullPath);
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $fullPath);
            finfo_close($finfo);
            
            $base64 = base64_encode($imageData);
            
            return [
                'base64' => 'data:' . $mimeType . ';base64,' . $base64,
                'mime_type' => $mimeType,
                'size' => strlen($imageData),
                'file_path' => $imagePath,
                'storage_url' => Storage::url($imagePath)
            ];
            
        } catch (\Exception $e) {
            Log::warning('Failed to convert image to base64', [
                'image_path' => $imagePath,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    /**
     * Display a listing of the resource with session integration.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            // Track API access in session
            Session::put('api_last_accessed', now());
            Session::put('api_access_count', Session::get('api_access_count', 0) + 1);
            
            $query = EscortModel::query();
            
            // Apply filters if provided
            if ($request->has('kategori') && $request->kategori) {
                $query->where('kategori_pengantar', $request->kategori);
            }
            
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }
            
            if ($request->has('jenis_kelamin') && $request->jenis_kelamin) {
                $query->where('jenis_kelamin', $request->jenis_kelamin);
            }
            
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nama_pengantar', 'like', "%{$search}%")
                      ->orWhere('nama_pasien', 'like', "%{$search}%")
                      ->orWhere('nomor_hp', 'like', "%{$search}%")
                      ->orWhere('plat_nomor', 'like', "%{$search}%");
                });
            }
            
            // Handle today only filter
            if ($request->has('today_only') && $request->today_only == '1') {
                $query->whereDate('created_at', today());
            }
            
            $perPage = $request->get('per_page', 15);
            $escorts = $query->orderBy('created_at', 'desc')->paginate($perPage);
            
            // Add base64 images to response if requested
            if ($request->has('include_images') && $request->include_images === 'base64') {
                $escorts->getCollection()->transform(function ($escort) {
                    $escort->foto_pengantar_base64 = $this->convertImageToBase64($escort->foto_pengantar);
                    return $escort;
                });
            }
            
            // Store API response stats in session
            Session::put('api_last_result_count', $escorts->count());
            Session::put('api_last_total', $escorts->total());
            
            return response()->json([
                'status' => 'success',
                'message' => 'Escorts retrieved successfully',
                'data' => $escorts,
                'session_id' => Session::getId(),
                'meta' => [
                    'current_page' => $escorts->currentPage(),
                    'total_pages' => $escorts->lastPage(),
                    'per_page' => $escorts->perPage(),
                    'total' => $escorts->total(),
                    'api_access_count' => Session::get('api_access_count', 0)
                ]
            ], 200);
        } catch (\Exception $e) {
            // Log error and store in session
            Log::error('API Index Error', [
                'error' => $e->getMessage(),
                'session_id' => Session::getId(),
                'request' => $request->all()
            ]);
            
            Session::put('api_last_error', [
                'message' => $e->getMessage(),
                'timestamp' => now(),
                'endpoint' => 'index'
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve escorts',
                'error' => $e->getMessage(),
                'session_id' => Session::getId()
            ], 500);
        }
    }

    /**
     * Store a newly created resource with enhanced session integration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Generate unique submission ID for tracking
        $submissionId = 'api_' . uniqid();
        Session::put("api_submission_{$submissionId}_started", now());
        Session::put("api_submission_{$submissionId}_ip", $request->ip());
        Session::put("api_submission_{$submissionId}_user_agent", $request->userAgent());
        
        DB::beginTransaction();
        
        try {
            // Enhanced validation with custom messages for base64 images
            $validatedData = $request->validate([
                'kategori_pengantar' => 'required|in:Polisi,Ambulans,Perorangan',
                'nama_pengantar' => 'required|string|max:255|min:3',
                'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
                'nomor_hp' => 'required|string|max:20|min:10|regex:/^[0-9+\-\s]+$/',
                'plat_nomor' => 'required|string|max:20|min:3',
                'nama_pasien' => 'required|string|max:255|min:3',
                'foto_pengantar_base64' => 'required|string',
                'foto_pengantar_info' => 'nullable|array',
                'foto_pengantar_info.name' => 'nullable|string',
                'foto_pengantar_info.size' => 'nullable|integer|max:2097152', // 2MB in bytes
                'foto_pengantar_info.type' => 'nullable|string|in:image/jpeg,image/png,image/jpg,image/gif',
                'status' => 'nullable|in:pending,verified,rejected'
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
                'foto_pengantar_base64.required' => 'Foto pengantar wajib diisi.',
                'foto_pengantar_info.size.max' => 'Ukuran foto maksimal 2MB.',
                'foto_pengantar_info.type.in' => 'Format foto harus berupa JPEG, PNG, JPG, atau GIF.',
                'status.in' => 'Status harus berupa pending, verified, atau rejected.',
            ]);
            
            // Handle base64 image processing using melihovv/base64-image-decoder
            if (isset($validatedData['foto_pengantar_base64'])) {
                $base64Image = $validatedData['foto_pengantar_base64'];
                $imageInfo = $validatedData['foto_pengantar_info'] ?? [];
                
                // Process base64 image using our helper method
                $imageResult = $this->processBase64Image($base64Image, $imageInfo, $submissionId);
                
                if ($imageResult['success']) {
                    $validatedData['foto_pengantar'] = $imageResult['file_path'];
                    
                    // Remove base64 fields from validated data
                    unset($validatedData['foto_pengantar_base64']);
                    unset($validatedData['foto_pengantar_info']);
                } else {
                    throw new \Exception('Gagal memproses gambar base64.');
                }
            }
            
            // Add tracking data
            $validatedData['submission_id'] = $submissionId;
            $validatedData['submitted_from_ip'] = $request->ip();
            $validatedData['api_submission'] = true;
            
            // Set default status if not provided
            if (!isset($validatedData['status'])) {
                $validatedData['status'] = 'pending';
            }
            
            // Create escort record
            $escort = EscortModel::create($validatedData);
            
            // Update session with success data
            Session::put("api_submission_{$submissionId}_completed", now());
            Session::put("api_submission_{$submissionId}_escort_id", $escort->id);
            Session::increment('api_submissions_count');
            
            // Update recent API submissions in session
            $recentApiSubmissions = Session::get('recent_api_submissions', []);
            array_unshift($recentApiSubmissions, [
                'id' => $escort->id,
                'submission_id' => $submissionId,
                'nama_pengantar' => $escort->nama_pengantar,
                'nama_pasien' => $escort->nama_pasien,
                'kategori' => $escort->kategori_pengantar,
                'submitted_at' => now(),
                'ip' => $request->ip()
            ]);
            
            // Keep only last 20 API submissions in session
            $recentApiSubmissions = array_slice($recentApiSubmissions, 0, 20);
            Session::put('recent_api_submissions', $recentApiSubmissions);
            
            // Clear cache
            Cache::forget('escort_stats');
            
            // Commit transaction
            DB::commit();
            
            // Log successful submission
            Log::info('API Escort submission successful', [
                'submission_id' => $submissionId,
                'escort_id' => $escort->id,
                'ip' => $request->ip(),
                'category' => $escort->kategori_pengantar
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Data escort berhasil ditambahkan melalui API',
                'data' => $escort,
                'submission_id' => $submissionId,
                'session_id' => Session::getId(),
                'meta' => [
                    'api_submissions_count' => Session::get('api_submissions_count', 0),
                    'timestamp' => now()
                ]
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            
            // Store validation errors in session
            Session::put("api_submission_{$submissionId}_failed", now());
            Session::put("api_submission_{$submissionId}_errors", $e->errors());
            
            Log::warning('API Escort validation failed', [
                'submission_id' => $submissionId,
                'errors' => $e->errors(),
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi data gagal',
                'errors' => $e->errors(),
                'submission_id' => $submissionId,
                'session_id' => Session::getId()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            // Store general error in session
            Session::put("api_submission_{$submissionId}_error", now());
            Session::put("api_submission_{$submissionId}_error_message", $e->getMessage());
            
            Log::error('API Escort submission failed', [
                'submission_id' => $submissionId,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data escort',
                'error' => $e->getMessage(),
                'submission_id' => $submissionId,
                'session_id' => Session::getId()
            ], 500);
        }
    }

    /**
     * Display the specified resource with session tracking.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $escort = EscortModel::findOrFail($id);
            
            // Track individual record access
            Session::put("escort_{$id}_last_viewed", now());
            Session::increment("escort_{$id}_view_count");
            
            // Add to recently viewed list
            $recentlyViewed = Session::get('recently_viewed_escorts', []);
            $recentlyViewed = array_filter($recentlyViewed, function($item) use ($id) {
                return $item['id'] != $id;
            });
            array_unshift($recentlyViewed, [
                'id' => $escort->id,
                'nama_pengantar' => $escort->nama_pengantar,
                'nama_pasien' => $escort->nama_pasien,
                'viewed_at' => now()
            ]);
            
            // Keep only last 10 viewed records
            $recentlyViewed = array_slice($recentlyViewed, 0, 10);
            Session::put('recently_viewed_escorts', $recentlyViewed);
            
            // Add base64 image to response by default for individual records
            $escort->foto_pengantar_base64 = $this->convertImageToBase64($escort->foto_pengantar);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Escort retrieved successfully',
                'data' => $escort,
                'session_id' => Session::getId(),
                'meta' => [
                    'view_count' => Session::get("escort_{$id}_view_count", 1),
                    'last_viewed' => Session::get("escort_{$id}_last_viewed"),
                    'has_base64_image' => !is_null($escort->foto_pengantar_base64)
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Track failed lookups
            Session::increment('api_failed_lookups');
            
            Log::warning('API Escort not found', [
                'escort_id' => $id,
                'ip' => request()->ip(),
                'session_id' => Session::getId()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Data escort tidak ditemukan',
                'session_id' => Session::getId()
            ], 404);
        } catch (\Exception $e) {
            Log::error('API Show Error', [
                'escort_id' => $id,
                'error' => $e->getMessage(),
                'session_id' => Session::getId()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data escort',
                'error' => $e->getMessage(),
                'session_id' => Session::getId()
            ], 500);
        }
    }

    /**
     * Update the specified resource with session tracking.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $escort = EscortModel::findOrFail($id);
            
            // Track update attempt
            $updateId = 'upd_' . uniqid();
            Session::put("api_update_{$updateId}_started", now());
            Session::put("api_update_{$updateId}_escort_id", $id);
            Session::put("api_update_{$updateId}_ip", $request->ip());
            
            $validatedData = $request->validate([
                'kategori_pengantar' => 'sometimes|required|in:Polisi,Ambulans,Perorangan',
                'nama_pengantar' => 'sometimes|required|string|max:255|min:3',
                'jenis_kelamin' => 'sometimes|required|in:Laki-laki,Perempuan',
                'nomor_hp' => 'sometimes|required|string|max:20|min:10',
                'plat_nomor' => 'sometimes|required|string|max:20|min:3',
                'nama_pasien' => 'sometimes|required|string|max:255|min:3',
                'foto_pengantar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'foto_pengantar_base64' => 'nullable|string',
                'foto_pengantar_info' => 'nullable|array',
                'foto_pengantar_info.name' => 'nullable|string',
                'foto_pengantar_info.size' => 'nullable|integer|max:2097152',
                'foto_pengantar_info.type' => 'nullable|string|in:image/jpeg,image/png,image/jpg,image/gif',
                'status' => 'sometimes|required|in:pending,verified,rejected'
            ]);
            
            // Store original data for comparison
            $originalData = $escort->toArray();
            
            // Handle base64 image upload (takes priority over file upload)
            if (isset($validatedData['foto_pengantar_base64'])) {
                $base64Image = $validatedData['foto_pengantar_base64'];
                $imageInfo = $validatedData['foto_pengantar_info'] ?? [];
                
                // Process base64 image using our helper method
                $imageResult = $this->processBase64Image($base64Image, $imageInfo, $updateId);
                
                if ($imageResult['success']) {
                    $validatedData['foto_pengantar'] = $imageResult['file_path'];
                    
                    // Remove base64 fields from validated data
                    unset($validatedData['foto_pengantar_base64']);
                    unset($validatedData['foto_pengantar_info']);
                } else {
                    throw new \Exception('Gagal memproses gambar base64.');
                }
            }
            // Handle regular file upload
            elseif ($request->hasFile('foto_pengantar')) {
                $file = $request->file('foto_pengantar');
                $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/uploads', $filename);
                $validatedData['foto_pengantar'] = 'uploads/' . $filename;
            }
            
            $escort->update($validatedData);
            
            // Track successful update
            Session::put("api_update_{$updateId}_completed", now());
            Session::put("api_update_{$updateId}_changes", array_diff_assoc($validatedData, $originalData));
            Session::increment('api_updates_count');
            
            // Clear cache
            Cache::forget('escort_stats');
            
            DB::commit();
            
            Log::info('API Escort updated', [
                'update_id' => $updateId,
                'escort_id' => $id,
                'changes' => array_keys($validatedData),
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Data escort berhasil diperbarui',
                'data' => $escort->fresh(),
                'update_id' => $updateId,
                'session_id' => Session::getId(),
                'meta' => [
                    'updated_fields' => array_keys($validatedData),
                    'api_updates_count' => Session::get('api_updates_count', 0)
                ]
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Data escort tidak ditemukan',
                'session_id' => Session::getId()
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi data gagal',
                'errors' => $e->errors(),
                'session_id' => Session::getId()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('API Update Error', [
                'escort_id' => $id,
                'error' => $e->getMessage(),
                'session_id' => Session::getId()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui data escort',
                'error' => $e->getMessage(),
                'session_id' => Session::getId()
            ], 500);
        }
    }

    /**
     * Remove the specified resource with session tracking.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $escort = EscortModel::findOrFail($id);
            
            // Store escort data before deletion
            $deletedData = $escort->toArray();
            $deleteId = 'del_' . uniqid();
            
            Session::put("api_delete_{$deleteId}_started", now());
            Session::put("api_delete_{$deleteId}_escort_data", $deletedData);
            Session::put("api_delete_{$deleteId}_ip", request()->ip());
            
            $escort->delete();
            
            // Track successful deletion
            Session::put("api_delete_{$deleteId}_completed", now());
            Session::increment('api_deletions_count');
            
            // Add to recently deleted list
            $recentlyDeleted = Session::get('recently_deleted_escorts', []);
            array_unshift($recentlyDeleted, [
                'id' => $deletedData['id'],
                'nama_pengantar' => $deletedData['nama_pengantar'],
                'nama_pasien' => $deletedData['nama_pasien'],
                'deleted_at' => now(),
                'delete_id' => $deleteId
            ]);
            
            // Keep only last 10 deleted records
            $recentlyDeleted = array_slice($recentlyDeleted, 0, 10);
            Session::put('recently_deleted_escorts', $recentlyDeleted);
            
            // Clear cache
            Cache::forget('escort_stats');
            
            DB::commit();
            
            Log::info('API Escort deleted', [
                'delete_id' => $deleteId,
                'escort_id' => $id,
                'escort_name' => $deletedData['nama_pengantar'],
                'ip' => request()->ip()
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Data escort berhasil dihapus',
                'delete_id' => $deleteId,
                'session_id' => Session::getId(),
                'meta' => [
                    'api_deletions_count' => Session::get('api_deletions_count', 0)
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Data escort tidak ditemukan',
                'session_id' => Session::getId()
            ], 404);
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('API Delete Error', [
                'escort_id' => $id,
                'error' => $e->getMessage(),
                'session_id' => Session::getId()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data escort',
                'error' => $e->getMessage(),
                'session_id' => Session::getId()
            ], 500);
        }
    }
    
    /**
     * Update escort status with session tracking
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $escort = EscortModel::findOrFail($id);
            
            // Track status update attempt
            $updateId = 'status_' . uniqid();
            Session::put("api_status_update_{$updateId}_started", now());
            Session::put("api_status_update_{$updateId}_escort_id", $id);
            Session::put("api_status_update_{$updateId}_ip", $request->ip());
            
            // Validate the status
            $request->validate([
                'status' => 'required|in:pending,verified,rejected'
            ]);
            
            $oldStatus = $escort->status;
            $newStatus = $request->status;
            
            // Update the escort status
            $escort->update([
                'status' => $newStatus
            ]);
            
            // Track successful status update
            Session::put("api_status_update_{$updateId}_completed", now());
            Session::put("api_status_update_{$updateId}_old_status", $oldStatus);
            Session::put("api_status_update_{$updateId}_new_status", $newStatus);
            Session::increment('api_status_updates_count');
            
            // Add to recent status updates in session
            $recentStatusUpdates = Session::get('recent_status_updates', []);
            array_unshift($recentStatusUpdates, [
                'escort_id' => $escort->id,
                'nama_pengantar' => $escort->nama_pengantar,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'updated_at' => now(),
                'update_id' => $updateId,
                'ip' => $request->ip()
            ]);
            
            // Keep only last 20 status updates in session
            $recentStatusUpdates = array_slice($recentStatusUpdates, 0, 20);
            Session::put('recent_status_updates', $recentStatusUpdates);
            
            // Clear cache
            Cache::forget('escort_stats');
            
            DB::commit();
            
            // Log the status change
            Log::info('API Escort status updated', [
                'update_id' => $updateId,
                'escort_id' => $escort->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Status escort berhasil diperbarui',
                'data' => [
                    'escort_id' => $escort->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'status_display' => $escort->getStatusDisplayName(),
                    'badge_class' => $escort->getStatusBadgeClass()
                ],
                'update_id' => $updateId,
                'session_id' => Session::getId(),
                'meta' => [
                    'api_status_updates_count' => Session::get('api_status_updates_count', 0),
                    'timestamp' => now()
                ]
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Data escort tidak ditemukan',
                'session_id' => Session::getId()
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi status gagal',
                'errors' => $e->errors(),
                'session_id' => Session::getId()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('API Status Update Error', [
                'escort_id' => $id,
                'error' => $e->getMessage(),
                'session_id' => Session::getId()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui status escort',
                'error' => $e->getMessage(),
                'session_id' => Session::getId()
            ], 500);
        }
    }

    /**
     * Get session statistics and activity
     */
    public function getSessionStats()
    {
        return response()->json([
            'status' => 'success',
            'session_id' => Session::getId(),
            'stats' => [
                'api_access_count' => Session::get('api_access_count', 0),
                'api_submissions_count' => Session::get('api_submissions_count', 0),
                'api_updates_count' => Session::get('api_updates_count', 0),
                'api_status_updates_count' => Session::get('api_status_updates_count', 0),
                'api_deletions_count' => Session::get('api_deletions_count', 0),
                'api_failed_lookups' => Session::get('api_failed_lookups', 0),
                'last_accessed' => Session::get('api_last_accessed'),
                'last_result_count' => Session::get('api_last_result_count', 0),
                'last_total' => Session::get('api_last_total', 0)
            ],
            'recent_activity' => [
                'submissions' => Session::get('recent_api_submissions', []),
                'viewed' => Session::get('recently_viewed_escorts', []),
                'deleted' => Session::get('recently_deleted_escorts', []),
                'status_updates' => Session::get('recent_status_updates', [])
            ]
        ]);
    }

    /**
     * Get dashboard statistics for authenticated users
     */
    public function getDashboardStats()
    {
        try {
            $stats = [
                'total_escorts' => EscortModel::count(),
                'today_submissions' => EscortModel::whereDate('created_at', today())->count(),
                'this_week_submissions' => EscortModel::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
                'this_month_submissions' => EscortModel::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'pending_count' => EscortModel::where('status', 'pending')->count(),
                'verified_count' => EscortModel::where('status', 'verified')->count(),
                'rejected_count' => EscortModel::where('status', 'rejected')->count(),
                'by_category' => EscortModel::selectRaw('kategori_pengantar, COUNT(*) as count')
                    ->groupBy('kategori_pengantar')
                    ->pluck('count', 'kategori_pengantar'),
                'by_status' => EscortModel::selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
                'recent_submissions' => EscortModel::latest()
                    ->limit(5)
                    ->select('id', 'nama_pengantar', 'nama_pasien', 'kategori_pengantar', 'status', 'created_at')
                    ->get()
            ];

            // Add session-specific stats
            $stats['session_info'] = [
                'session_id' => Session::getId(),
                'api_access_count' => Session::get('api_access_count', 0),
                'user_submissions' => Session::get('api_submissions_count', 0),
                'status_updates' => Session::get('api_status_updates_count', 0),
                'last_accessed' => Session::get('api_last_accessed')
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Dashboard stats retrieved successfully',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard Stats Error', [
                'error' => $e->getMessage(),
                'session_id' => Session::getId()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve dashboard stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get image as base64 for a specific escort
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function getImageBase64($id)
    {
        try {
            $escort = EscortModel::findOrFail($id);
            
            if (!$escort->foto_pengantar) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Escort tidak memiliki foto'
                ], 404);
            }
            
            $base64Image = $this->convertImageToBase64($escort->foto_pengantar);
            
            if (!$base64Image) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengkonversi gambar ke base64'
                ], 500);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Image converted to base64 successfully',
                'data' => [
                    'escort_id' => $escort->id,
                    'nama_pengantar' => $escort->nama_pengantar,
                    'image_base64' => $base64Image['base64'],
                    'mime_type' => $base64Image['mime_type'],
                    'size' => $base64Image['size'],
                    'storage_url' => $base64Image['storage_url']
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Escort tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Get Image Base64 Error', [
                'escort_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil gambar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload image as base64 for existing escort
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function uploadImageBase64(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $escort = EscortModel::findOrFail($id);
            
            $request->validate([
                'foto_pengantar_base64' => 'required|string',
                'foto_pengantar_info' => 'nullable|array',
                'foto_pengantar_info.name' => 'nullable|string',
                'foto_pengantar_info.size' => 'nullable|integer|max:2097152',
                'foto_pengantar_info.type' => 'nullable|string|in:image/jpeg,image/png,image/jpg,image/gif'
            ]);
            
            $base64Image = $request->foto_pengantar_base64;
            $imageInfo = $request->foto_pengantar_info ?? [];
            
            // Generate upload ID for tracking
            $uploadId = 'img_' . uniqid();
            
            // Process base64 image
            $imageResult = $this->processBase64Image($base64Image, $imageInfo, $uploadId);
            
            if ($imageResult['success']) {
                // Delete old image if exists
                if ($escort->foto_pengantar) {
                    Storage::delete('public/' . $escort->foto_pengantar);
                }
                
                // Update escort with new image
                $escort->update([
                    'foto_pengantar' => $imageResult['file_path']
                ]);
                
                // Track successful upload
                Session::increment('api_image_uploads_count');
                
                // Clear cache
                Cache::forget('escort_stats');
                
                DB::commit();
                
                Log::info('API Image uploaded via base64', [
                    'escort_id' => $escort->id,
                    'upload_id' => $uploadId,
                    'file_size' => $imageResult['file_info']['size'],
                    'format' => $imageResult['file_info']['format']
                ]);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Gambar berhasil diupload via base64',
                    'data' => [
                        'escort_id' => $escort->id,
                        'image_path' => $imageResult['file_path'],
                        'file_info' => $imageResult['file_info'],
                        'upload_id' => $uploadId,
                        'storage_url' => Storage::url($imageResult['file_path'])
                    ]
                ], 200);
            } else {
                throw new \Exception('Gagal memproses gambar base64.');
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Escort tidak ditemukan'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi data gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Upload Image Base64 Error', [
                'escort_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengupload gambar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
