<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GoApiService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QrCodeGoController extends Controller
{
    protected $goApiService;

    public function __construct(GoApiService $goApiService)
    {
        $this->goApiService = $goApiService;
    }

    /**
     * Generate QR code for form submission URL via Go API (GET method - returns PNG)
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

            // Validate input parameters
            $validator = Validator::make($request->all(), [
                'url' => 'nullable|url',
                'size' => 'nullable|integer|min:64|max:1024'
            ]);

            if ($validator->fails()) {
                Log::warning('Go API Proxy QR Code validation failed', [
                    'errors' => $validator->errors(),
                    'ip' => $request->ip()
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi parameter gagal',
                    'errors' => $validator->errors(),
                    'laravel_session_id' => Session::getId()
                ], 422);
            }

            // Prepare parameters for Go API
            $params = [];
            if ($request->has('url')) {
                $params['url'] = $request->url;
            }
            if ($request->has('size')) {
                $params['size'] = $request->size;
            }

            // Call Go API to get PNG QR code
            $qrCodeData = $this->goApiService->generateQrCodePng($params);

            // Log QR code generation
            Log::info('Go API Proxy QR Code generated for form', [
                'url' => $params['url'] ?? 'default',
                'size' => $params['size'] ?? 'default',
                'ip' => $request->ip(),
                'timestamp' => now()
            ]);

            // Return QR code as PNG image
            return response($qrCodeData)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'inline; filename="form-qrcode.png"')
                ->header('Cache-Control', 'public, max-age=3600') // Cache for 1 hour
                ->header('X-Laravel-Session-ID', Session::getId())
                ->header('X-QR-Generation-Count', Session::get('qr_generation_count', 0));

        } catch (\Exception $e) {
            Log::error('Go API Proxy QR Code generation failed', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return error response as JSON if requested, otherwise return simple error image
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal generate QR code via Go API',
                    'error' => $e->getMessage(),
                    'laravel_session_id' => Session::getId()
                ], 500);
            }

            // Return a simple error image as PNG
            $errorImage = $this->generateErrorImage();
            return response($errorImage)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'inline; filename="error-qrcode.png"');
        }
    }

    /**
     * Generate QR code via Go API (POST method - returns JSON with base64)
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateFormQrCodeJson(Request $request)
    {
        try {
            // Track QR code generation in session
            Session::put('qr_generated_at', now());
            Session::put('qr_generated_ip', $request->ip());
            Session::increment('qr_generation_count');

            // Validate input parameters
            $validator = Validator::make($request->all(), [
                'url' => 'nullable|url',
                'size' => 'nullable|integer|min:64|max:1024'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi parameter gagal',
                    'errors' => $validator->errors(),
                    'laravel_session_id' => Session::getId()
                ], 422);
            }

            // Call Go API to get JSON QR code response
            $result = $this->goApiService->generateQrCodeJson($validator->validated());

            // Add Laravel session info to response
            $result['laravel_session_id'] = Session::getId();
            $result['laravel_meta'] = [
                'qr_generation_count' => Session::get('qr_generation_count', 0),
                'proxy_timestamp' => now()
            ];

            Log::info('Go API Proxy QR Code JSON generated', [
                'url' => $request->url ?? 'default',
                'size' => $request->size ?? 'default',
                'ip' => $request->ip(),
                'timestamp' => now()
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Go API Proxy QR Code JSON generation failed', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal generate QR code JSON via Go API',
                'error' => $e->getMessage(),
                'laravel_session_id' => Session::getId()
            ], 500);
        }
    }

    /**
     * Generate a simple error image when QR code generation fails
     *
     * @return string Binary PNG data
     */
    private function generateErrorImage(): string
    {
        // Create a simple 200x200 error image
        $width = 200;
        $height = 200;
        
        $image = imagecreate($width, $height);
        
        // Define colors
        $backgroundColor = imagecolorallocate($image, 255, 235, 238); // Light red background
        $textColor = imagecolorallocate($image, 244, 67, 54); // Red text
        $borderColor = imagecolorallocate($image, 244, 67, 54); // Red border
        
        // Fill background
        imagefill($image, 0, 0, $backgroundColor);
        
        // Draw border
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $borderColor);
        
        // Add error text
        $text1 = 'QR Error';
        $text2 = 'Go API';
        $text3 = 'Unavailable';
        
        // Calculate text position (center)
        $font = 3; // Built-in font
        $text1Width = imagefontwidth($font) * strlen($text1);
        $text2Width = imagefontwidth($font) * strlen($text2);
        $text3Width = imagefontwidth($font) * strlen($text3);
        
        $x1 = ($width - $text1Width) / 2;
        $x2 = ($width - $text2Width) / 2;
        $x3 = ($width - $text3Width) / 2;
        
        $y1 = $height / 2 - 30;
        $y2 = $height / 2;
        $y3 = $height / 2 + 20;
        
        // Draw text
        imagestring($image, $font, $x1, $y1, $text1, $textColor);
        imagestring($image, $font, $x2, $y2, $text2, $textColor);
        imagestring($image, $font, $x3, $y3, $text3, $textColor);
        
        // Capture image as PNG
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();
        
        // Clean up
        imagedestroy($image);
        
        return $imageData;
    }
}