<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        // Redirect to dashboard if already authenticated
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle login authentication.
     */
    public function login(Request $request)
    {
        // Validate the login form
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Rate limiting to prevent brute force attacks
        $key = 'login_attempts_' . $request->ip();
        $maxAttempts = 5;
        $decayMinutes = 15;

        if (cache()->has($key) && cache()->get($key) >= $maxAttempts) {
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . $decayMinutes . ' menit.',
            ]);
        }

        // Attempt to authenticate
        $remember = $request->boolean('remember');
        
        if (Auth::attempt($credentials, $remember)) {
            // Clear login attempts on successful login
            cache()->forget($key);
            
            $request->session()->regenerate();

            // Redirect to intended URL or dashboard
            return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang! Anda berhasil masuk.');
        }

        // Increment login attempts
        $attempts = cache()->get($key, 0) + 1;
        cache()->put($key, $attempts, now()->addMinutes($decayMinutes));

        // Authentication failed
        throw ValidationException::withMessages([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ]);
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }

    /**
     * Create default admin user if it doesn't exist.
     * This method can be called from a seeder or artisan command.
     */
    public function createDefaultAdmin()
    {
        $adminEmail = 'admin@igd.com';
        
        if (!User::where('email', $adminEmail)->exists()) {
            User::create([
                'name' => 'Administrator',
                'email' => $adminEmail,
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]);
            
            return "Default admin user created successfully. Email: {$adminEmail}, Password: admin123";
        }
        
        return "Admin user already exists.";
    }
}