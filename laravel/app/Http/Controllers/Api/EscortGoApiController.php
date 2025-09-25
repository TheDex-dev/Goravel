<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GoApiService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EscortGoApiController extends Controller
{
    protected $goApiService;

    public function __construct(GoApiService $goApiService)
    {
        $this->goApiService = $goApiService;
    }

    /**
     * Display a listing of the resource via Go API.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            // Track API access in session
            Session::put('api_last_accessed', now());
            Session::put('api_access_count', Session::get('api_access_count', 0) + 1);

            // Prepare query parameters for Go API
            $params = [];
            
            if ($request->has('kategori_pengantar') && $request->kategori_pengantar) {
                $params['kategori_pengantar'] = $request->kategori_pengantar;
            }
            
            if ($request->has('status') && $request->status) {
                $params['status'] = $request->status;
            }
            
            if ($request->has('jenis_kelamin') && $request->jenis_kelamin) {
                $params['jenis_kelamin'] = $request->jenis_kelamin;
            }
            
            if ($request->has('search') && $request->search) {
                $params['search'] = $request->search;
            }
            
            if ($request->has('today_only') && $request->today_only == '1') {
                $params['today_only'] = '1';
            }
            
            if ($request->has('page')) {
                $params['page'] = $request->page;
            }
            
            if ($request->has('per_page')) {
                $params['per_page'] = $request->per_page;
            }
            
            if ($request->has('sort_by')) {
                $params['sort_by'] = $request->sort_by;
            }
            
            if ($request->has('sort_order')) {
                $params['sort_order'] = $request->sort_order;
            }
            
            if ($request->has('include_images') && $request->include_images === 'base64') {
                $params['include_images'] = 'base64';
            }

            // Call Go API
            $result = $this->goApiService->getEscorts($params);

            // Store API response stats in session
            if (isset($result['meta'])) {
                Session::put('api_last_result_count', $result['meta']['per_page'] ?? 0);
                Session::put('api_last_total', $result['meta']['total'] ?? 0);
            }

            // Add Laravel session info to response
            $result['laravel_session_id'] = Session::getId();
            $result['laravel_meta'] = [
                'api_access_count' => Session::get('api_access_count', 0),
                'proxy_timestamp' => now()
            ];

            return response()->json($result);

        } catch (\Exception $e) {
            // Log error and store in session
            Log::error('Go API Proxy Index Error', [
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
                'message' => 'Failed to retrieve escorts from Go API',
                'error' => $e->getMessage(),
                'laravel_session_id' => Session::getId()
            ], 500);
        }
    }

    /**
     * Store a newly created resource via Go API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Generate unique submission ID for tracking
        $submissionId = 'laravel_' . uniqid();
        Session::put("api_submission_{$submissionId}_started", now());
        Session::put("api_submission_{$submissionId}_ip", $request->ip());
        Session::put("api_submission_{$submissionId}_user_agent", $request->userAgent());

        try {
            // Validate input according to Go API expectations
            $validator = Validator::make($request->all(), [
                'kategori_pengantar' => 'required|in:Polisi,Ambulans,Perorangan',
                'nama_pengantar' => 'required|string|max:255|min:3',
                'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
                'nomor_hp' => 'required|string|max:20|min:10',
                'plat_nomor' => 'required|string|max:20|min:3',
                'nama_pasien' => 'required|string|max:255|min:3',
                'foto_pengantar_base64' => 'nullable|string',
                'status' => 'nullable|in:pending,verified,rejected'
            ], [
                'nama_pengantar.required' => 'Nama pengantar wajib diisi.',
                'nama_pengantar.min' => 'Nama pengantar minimal 3 karakter.',
                'nomor_hp.required' => 'Nomor HP wajib diisi.',
                'nomor_hp.min' => 'Nomor HP minimal 10 karakter.',
                'plat_nomor.required' => 'Plat nomor wajib diisi.',
                'plat_nomor.min' => 'Plat nomor minimal 3 karakter.',
                'nama_pasien.required' => 'Nama pasien wajib diisi.',
                'nama_pasien.min' => 'Nama pasien minimal 3 karakter.',
                'status.in' => 'Status harus berupa pending, verified, atau rejected.',
            ]);

            if ($validator->fails()) {
                Session::put("api_submission_{$submissionId}_failed", now());
                Session::put("api_submission_{$submissionId}_errors", $validator->errors());

                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi data gagal',
                    'errors' => $validator->errors(),
                    'submission_id' => $submissionId,
                    'laravel_session_id' => Session::getId()
                ], 422);
            }

            // Prepare data for Go API
            $data = $validator->validated();

            // Call Go API
            $result = $this->goApiService->createEscort($data);

            // Update session with success data
            Session::put("api_submission_{$submissionId}_completed", now());
            if (isset($result['data']['id'])) {
                Session::put("api_submission_{$submissionId}_escort_id", $result['data']['id']);
            }
            Session::increment('api_submissions_count');

            // Update recent API submissions in session
            $recentApiSubmissions = Session::get('recent_api_submissions', []);
            array_unshift($recentApiSubmissions, [
                'id' => $result['data']['id'] ?? null,
                'submission_id' => $submissionId,
                'nama_pengantar' => $data['nama_pengantar'],
                'nama_pasien' => $data['nama_pasien'],
                'kategori' => $data['kategori_pengantar'],
                'submitted_at' => now(),
                'ip' => $request->ip(),
                'via' => 'go_api'
            ]);

            // Keep only last 20 API submissions in session
            $recentApiSubmissions = array_slice($recentApiSubmissions, 0, 20);
            Session::put('recent_api_submissions', $recentApiSubmissions);

            // Add Laravel session info to response
            $result['submission_id'] = $submissionId;
            $result['laravel_session_id'] = Session::getId();
            $result['laravel_meta'] = [
                'api_submissions_count' => Session::get('api_submissions_count', 0),
                'proxy_timestamp' => now()
            ];

            Log::info('Go API Proxy Escort submission successful', [
                'submission_id' => $submissionId,
                'escort_id' => $result['data']['id'] ?? null,
                'ip' => $request->ip(),
                'category' => $data['kategori_pengantar']
            ]);

            return response()->json($result, 201);

        } catch (\Exception $e) {
            // Store general error in session
            Session::put("api_submission_{$submissionId}_error", now());
            Session::put("api_submission_{$submissionId}_error_message", $e->getMessage());

            Log::error('Go API Proxy Escort submission failed', [
                'submission_id' => $submissionId,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data escort via Go API',
                'error' => $e->getMessage(),
                'submission_id' => $submissionId,
                'laravel_session_id' => Session::getId()
            ], 500);
        }
    }

    /**
     * Display the specified resource via Go API.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            // Track individual record access
            Session::put("escort_{$id}_last_viewed", now());
            Session::increment("escort_{$id}_view_count");

            // Call Go API
            $result = $this->goApiService->getEscort($id);

            // Add to recently viewed list
            $recentlyViewed = Session::get('recently_viewed_escorts', []);
            $recentlyViewed = array_filter($recentlyViewed, function($item) use ($id) {
                return $item['id'] != $id;
            });

            if (isset($result['data'])) {
                array_unshift($recentlyViewed, [
                    'id' => $result['data']['id'],
                    'nama_pengantar' => $result['data']['nama_pengantar'] ?? '',
                    'nama_pasien' => $result['data']['nama_pasien'] ?? '',
                    'viewed_at' => now(),
                    'via' => 'go_api'
                ]);
            }

            // Keep only last 10 viewed records
            $recentlyViewed = array_slice($recentlyViewed, 0, 10);
            Session::put('recently_viewed_escorts', $recentlyViewed);

            // Add Laravel session info to response
            $result['laravel_session_id'] = Session::getId();
            $result['laravel_meta'] = [
                'view_count' => Session::get("escort_{$id}_view_count", 1),
                'last_viewed' => Session::get("escort_{$id}_last_viewed"),
                'proxy_timestamp' => now()
            ];

            return response()->json($result);

        } catch (\Exception $e) {
            // Track failed lookups
            Session::increment('api_failed_lookups');

            Log::warning('Go API Proxy Escort not found', [
                'escort_id' => $id,
                'ip' => request()->ip(),
                'session_id' => Session::getId(),
                'error' => $e->getMessage()
            ]);

            $statusCode = 500;
            if ($e->getCode() == 404) {
                $statusCode = 404;
            }

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'laravel_session_id' => Session::getId()
            ], $statusCode);
        }
    }

    /**
     * Update the specified resource via Go API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            // Track update attempt
            $updateId = 'upd_' . uniqid();
            Session::put("api_update_{$updateId}_started", now());
            Session::put("api_update_{$updateId}_escort_id", $id);
            Session::put("api_update_{$updateId}_ip", $request->ip());

            // Validate input
            $validator = Validator::make($request->all(), [
                'kategori_pengantar' => 'sometimes|required|in:Polisi,Ambulans,Perorangan',
                'nama_pengantar' => 'sometimes|required|string|max:255|min:3',
                'jenis_kelamin' => 'sometimes|required|in:Laki-laki,Perempuan',
                'nomor_hp' => 'sometimes|required|string|max:20|min:10',
                'plat_nomor' => 'sometimes|required|string|max:20|min:3',
                'nama_pasien' => 'sometimes|required|string|max:255|min:3',
                'foto_pengantar_base64' => 'nullable|string',
                'status' => 'sometimes|required|in:pending,verified,rejected'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi data gagal',
                    'errors' => $validator->errors(),
                    'laravel_session_id' => Session::getId()
                ], 422);
            }

            // Call Go API
            $result = $this->goApiService->updateEscort($id, $validator->validated());

            // Track successful update
            Session::put("api_update_{$updateId}_completed", now());
            Session::increment('api_updates_count');

            // Add Laravel session info to response
            $result['update_id'] = $updateId;
            $result['laravel_session_id'] = Session::getId();
            $result['laravel_meta'] = [
                'updated_fields' => array_keys($validator->validated()),
                'api_updates_count' => Session::get('api_updates_count', 0),
                'proxy_timestamp' => now()
            ];

            Log::info('Go API Proxy Escort updated', [
                'update_id' => $updateId,
                'escort_id' => $id,
                'changes' => array_keys($validator->validated()),
                'ip' => $request->ip()
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Go API Proxy Update Error', [
                'escort_id' => $id,
                'error' => $e->getMessage(),
                'session_id' => Session::getId()
            ]);

            $statusCode = 500;
            if ($e->getCode() == 404) {
                $statusCode = 404;
            }

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'laravel_session_id' => Session::getId()
            ], $statusCode);
        }
    }

    /**
     * Remove the specified resource via Go API.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // Store deletion tracking info
            $deleteId = 'del_' . uniqid();
            Session::put("api_delete_{$deleteId}_started", now());
            Session::put("api_delete_{$deleteId}_escort_id", $id);
            Session::put("api_delete_{$deleteId}_ip", request()->ip());

            // Call Go API
            $result = $this->goApiService->deleteEscort($id);

            // Track successful deletion
            Session::put("api_delete_{$deleteId}_completed", now());
            Session::increment('api_deletions_count');

            // Add Laravel session info to response
            $result['delete_id'] = $deleteId;
            $result['laravel_session_id'] = Session::getId();
            $result['laravel_meta'] = [
                'api_deletions_count' => Session::get('api_deletions_count', 0),
                'proxy_timestamp' => now()
            ];

            Log::info('Go API Proxy Escort deleted', [
                'delete_id' => $deleteId,
                'escort_id' => $id,
                'ip' => request()->ip()
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Go API Proxy Delete Error', [
                'escort_id' => $id,
                'error' => $e->getMessage(),
                'session_id' => Session::getId()
            ]);

            $statusCode = 500;
            if ($e->getCode() == 404) {
                $statusCode = 404;
            }

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'laravel_session_id' => Session::getId()
            ], $statusCode);
        }
    }

    /**
     * Update escort status via Go API
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            // Track status update attempt
            $updateId = 'status_' . uniqid();
            Session::put("api_status_update_{$updateId}_started", now());
            Session::put("api_status_update_{$updateId}_escort_id", $id);
            Session::put("api_status_update_{$updateId}_ip", $request->ip());

            // Validate the status
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,verified,rejected'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi status gagal',
                    'errors' => $validator->errors(),
                    'laravel_session_id' => Session::getId()
                ], 422);
            }

            // Call Go API
            $result = $this->goApiService->updateEscortStatus($id, $request->status);

            // Track successful status update
            Session::put("api_status_update_{$updateId}_completed", now());
            Session::increment('api_status_updates_count');

            // Add to recent status updates in session
            $recentStatusUpdates = Session::get('recent_status_updates', []);
            array_unshift($recentStatusUpdates, [
                'escort_id' => $id,
                'new_status' => $request->status,
                'updated_at' => now(),
                'update_id' => $updateId,
                'ip' => $request->ip(),
                'via' => 'go_api'
            ]);

            // Keep only last 20 status updates in session
            $recentStatusUpdates = array_slice($recentStatusUpdates, 0, 20);
            Session::put('recent_status_updates', $recentStatusUpdates);

            // Add Laravel session info to response
            $result['update_id'] = $updateId;
            $result['laravel_session_id'] = Session::getId();
            $result['laravel_meta'] = [
                'api_status_updates_count' => Session::get('api_status_updates_count', 0),
                'proxy_timestamp' => now()
            ];

            Log::info('Go API Proxy Escort status updated', [
                'update_id' => $updateId,
                'escort_id' => $id,
                'new_status' => $request->status,
                'ip' => $request->ip()
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Go API Proxy Status Update Error', [
                'escort_id' => $id,
                'error' => $e->getMessage(),
                'session_id' => Session::getId()
            ]);

            $statusCode = 500;
            if ($e->getCode() == 404) {
                $statusCode = 404;
            }

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'laravel_session_id' => Session::getId()
            ], $statusCode);
        }
    }

    /**
     * Get dashboard statistics via Go API
     */
    public function getDashboardStats()
    {
        try {
            // Call Go API
            $result = $this->goApiService->getDashboardStats();

            // Add Laravel session-specific stats
            if (isset($result['data'])) {
                $result['data']['laravel_session_info'] = [
                    'session_id' => Session::getId(),
                    'api_access_count' => Session::get('api_access_count', 0),
                    'user_submissions' => Session::get('api_submissions_count', 0),
                    'status_updates' => Session::get('api_status_updates_count', 0),
                    'last_accessed' => Session::get('api_last_accessed'),
                    'proxy_timestamp' => now()
                ];
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Go API Proxy Dashboard Stats Error', [
                'error' => $e->getMessage(),
                'session_id' => Session::getId()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve dashboard stats from Go API',
                'error' => $e->getMessage(),
                'laravel_session_id' => Session::getId()
            ], 500);
        }
    }

    /**
     * Get image as base64 via Go API
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function getImageBase64($id)
    {
        try {
            // Call Go API
            $result = $this->goApiService->getImageBase64($id);

            // Add Laravel session info to response
            $result['laravel_session_id'] = Session::getId();
            $result['laravel_meta'] = [
                'proxy_timestamp' => now()
            ];

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Go API Proxy Get Image Base64 Error', [
                'escort_id' => $id,
                'error' => $e->getMessage()
            ]);

            $statusCode = 500;
            if ($e->getCode() == 404) {
                $statusCode = 404;
            }

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'laravel_session_id' => Session::getId()
            ], $statusCode);
        }
    }

    /**
     * Upload image as base64 via Go API
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function uploadImageBase64(Request $request, $id)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'foto_pengantar_base64' => 'required|string',
                'foto_pengantar_info' => 'nullable|array',
                'foto_pengantar_info.name' => 'nullable|string',
                'foto_pengantar_info.size' => 'nullable|integer|max:2097152',
                'foto_pengantar_info.type' => 'nullable|string|in:image/jpeg,image/png,image/jpg,image/gif'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi data gagal',
                    'errors' => $validator->errors(),
                    'laravel_session_id' => Session::getId()
                ], 422);
            }

            // Call Go API
            $result = $this->goApiService->uploadImageBase64($id, $validator->validated());

            // Track successful upload
            Session::increment('api_image_uploads_count');

            // Add Laravel session info to response
            $result['laravel_session_id'] = Session::getId();
            $result['laravel_meta'] = [
                'api_image_uploads_count' => Session::get('api_image_uploads_count', 0),
                'proxy_timestamp' => now()
            ];

            Log::info('Go API Proxy Image uploaded via base64', [
                'escort_id' => $id,
                'upload_id' => $result['data']['upload_id'] ?? null
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Go API Proxy Upload Image Base64 Error', [
                'escort_id' => $id,
                'error' => $e->getMessage()
            ]);

            $statusCode = 500;
            if ($e->getCode() == 404) {
                $statusCode = 404;
            }

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'laravel_session_id' => Session::getId()
            ], $statusCode);
        }
    }

    /**
     * Get session statistics (combination of Laravel session + Go API session stats)
     */
    public function getSessionStats()
    {
        try {
            // Get Go API session stats
            $goStats = $this->goApiService->getSessionStats();

            // Combine with Laravel session stats
            $combinedStats = [
                'laravel_session_id' => Session::getId(),
                'laravel_stats' => [
                    'api_access_count' => Session::get('api_access_count', 0),
                    'api_submissions_count' => Session::get('api_submissions_count', 0),
                    'api_updates_count' => Session::get('api_updates_count', 0),
                    'api_status_updates_count' => Session::get('api_status_updates_count', 0),
                    'api_deletions_count' => Session::get('api_deletions_count', 0),
                    'api_failed_lookups' => Session::get('api_failed_lookups', 0),
                    'api_image_uploads_count' => Session::get('api_image_uploads_count', 0),
                    'last_accessed' => Session::get('api_last_accessed'),
                    'last_result_count' => Session::get('api_last_result_count', 0),
                    'last_total' => Session::get('api_last_total', 0)
                ],
                'laravel_recent_activity' => [
                    'submissions' => Session::get('recent_api_submissions', []),
                    'viewed' => Session::get('recently_viewed_escorts', []),
                    'status_updates' => Session::get('recent_status_updates', [])
                ],
                'go_api_stats' => $goStats,
                'proxy_timestamp' => now()
            ];

            return response()->json([
                'status' => 'success',
                'data' => $combinedStats
            ]);

        } catch (\Exception $e) {
            Log::error('Go API Proxy Session Stats Error', [
                'error' => $e->getMessage(),
                'session_id' => Session::getId()
            ]);

            // Return Laravel-only stats if Go API fails
            return response()->json([
                'status' => 'partial_success',
                'message' => 'Go API stats unavailable, returning Laravel stats only',
                'data' => [
                    'laravel_session_id' => Session::getId(),
                    'laravel_stats' => [
                        'api_access_count' => Session::get('api_access_count', 0),
                        'api_submissions_count' => Session::get('api_submissions_count', 0),
                        'api_updates_count' => Session::get('api_updates_count', 0),
                        'api_status_updates_count' => Session::get('api_status_updates_count', 0),
                        'api_deletions_count' => Session::get('api_deletions_count', 0),
                        'api_failed_lookups' => Session::get('api_failed_lookups', 0),
                        'api_image_uploads_count' => Session::get('api_image_uploads_count', 0),
                        'last_accessed' => Session::get('api_last_accessed'),
                        'last_result_count' => Session::get('api_last_result_count', 0),
                        'last_total' => Session::get('api_last_total', 0)
                    ],
                    'go_api_error' => $e->getMessage(),
                    'proxy_timestamp' => now()
                ]
            ], 206); // 206 Partial Content
        }
    }
}