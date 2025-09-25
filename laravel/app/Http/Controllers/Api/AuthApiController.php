<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthApiController extends Controller
{
    /**
     * Handle API login with session authentication
     * This will create a session for use with subsequent API calls
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Rate limiting
        $key = 'api_login_attempts_' . $request->ip();
        $maxAttempts = 5;
        $decayMinutes = 15;

        if (cache()->has($key) && cache()->get($key) >= $maxAttempts) {
            return response()->json([
                'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . $decayMinutes . ' menit.',
                'errors' => ['email' => ['Rate limit exceeded']]
            ], 429);
        }

        // Attempt authentication
        if (Auth::attempt($request->only('email', 'password'))) {
            cache()->forget($key);
            $request->session()->regenerate();
            
            return response()->json([
                'message' => 'Login berhasil',
                'user' => Auth::user(),
                'csrf_token' => csrf_token()
            ]);
        }

        // Increment failed attempts
        $attempts = cache()->get($key, 0) + 1;
        cache()->put($key, $attempts, now()->addMinutes($decayMinutes));

        return response()->json([
            'message' => 'Email atau password yang Anda masukkan salah.',
            'errors' => ['email' => ['Invalid credentials']]
        ], 401);
    }

    /**
     * Handle API logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Get current authenticated user
     */
    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'csrf_token' => csrf_token()
        ]);
    }

    /**
     * Verify authentication status
     */
    public function check(Request $request)
    {
        return response()->json([
            'authenticated' => Auth::check(),
            'user' => Auth::user(),
            'csrf_token' => csrf_token()
        ]);
    }

    /**
     * Get CSRF token for API requests
     */
    public function csrfToken()
    {
        return response()->json([
            'csrf_token' => csrf_token()
        ]);
    }

    /**
     * Initialize sanctum session for SPA
     * This endpoint should be called before making authenticated API requests
     */
    public function sanctum(Request $request)
    {
        return response()->json([
            'message' => 'Sanctum session initialized',
            'csrf_token' => csrf_token(),
            'authenticated' => Auth::check(),
            'user' => Auth::user()
        ]);
    }
}