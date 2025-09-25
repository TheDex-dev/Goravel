<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiRoutingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * This middleware can route requests to either Go API or Laravel based on:
     * 1. Request header 'X-Use-Go-API' 
     * 2. Environment variable GO_API_ENABLED
     * 3. Query parameter 'use_go_api'
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if Go API should be used
        $useGoApi = $this->shouldUseGoApi($request);

        // Add routing decision to request attributes for use in controllers
        $request->attributes->set('use_go_api', $useGoApi);
        $request->attributes->set('routing_decision_reason', $this->getRoutingReason($request));

        // Log routing decision for debugging
        Log::debug('API Routing Decision', [
            'use_go_api' => $useGoApi,
            'reason' => $request->attributes->get('routing_decision_reason'),
            'request_path' => $request->path(),
            'request_method' => $request->method(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip()
        ]);

        // Add headers to response to indicate routing decision
        $response = $next($request);

        if (method_exists($response, 'header')) {
            $response->header('X-API-Backend', $useGoApi ? 'go' : 'laravel');
            $response->header('X-Routing-Reason', $request->attributes->get('routing_decision_reason'));
        }

        return $response;
    }

    /**
     * Determine if Go API should be used
     *
     * @param Request $request
     * @return bool
     */
    private function shouldUseGoApi(Request $request): bool
    {
        // Priority 1: Explicit request header
        if ($request->hasHeader('X-Use-Go-API')) {
            return $request->header('X-Use-Go-API') === 'true';
        }

        // Priority 2: Query parameter (useful for testing)
        if ($request->has('use_go_api')) {
            return $request->query('use_go_api') === 'true' || $request->query('use_go_api') === '1';
        }

        // Priority 3: Environment configuration
        $goApiEnabled = env('GO_API_ENABLED', 'true');
        if ($goApiEnabled === 'false' || $goApiEnabled === '0') {
            return false;
        }

        // Priority 4: Check if Go API is available (health check)
        if (env('GO_API_AUTO_DETECT', 'true') === 'true') {
            return $this->isGoApiAvailable();
        }

        // Default: Use Go API if enabled
        return $goApiEnabled === 'true' || $goApiEnabled === '1';
    }

    /**
     * Get the reason for the routing decision
     *
     * @param Request $request
     * @return string
     */
    private function getRoutingReason(Request $request): string
    {
        if ($request->hasHeader('X-Use-Go-API')) {
            $headerValue = $request->header('X-Use-Go-API');
            return "header_explicit:{$headerValue}";
        }

        if ($request->has('use_go_api')) {
            $queryValue = $request->query('use_go_api');
            return "query_param:{$queryValue}";
        }

        $goApiEnabled = env('GO_API_ENABLED', 'true');
        if ($goApiEnabled === 'false' || $goApiEnabled === '0') {
            return "env_disabled:{$goApiEnabled}";
        }

        if (env('GO_API_AUTO_DETECT', 'true') === 'true') {
            $available = $this->isGoApiAvailable();
            return "auto_detect:" . ($available ? 'available' : 'unavailable');
        }

        return "env_enabled:{$goApiEnabled}";
    }

    /**
     * Check if Go API is available via health check
     *
     * @return bool
     */
    private function isGoApiAvailable(): bool
    {
        static $lastCheck = null;
        static $lastResult = null;
        static $checkInterval = 60; // Check every 60 seconds

        $now = time();

        // Use cached result if within check interval
        if ($lastCheck !== null && ($now - $lastCheck) < $checkInterval && $lastResult !== null) {
            return $lastResult;
        }

        try {
            $goApiUrl = config('services.go_api.url', env('GO_API_URL', 'http://localhost:8080'));
            $timeout = config('services.go_api.timeout', env('GO_API_TIMEOUT', 5));

            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => $timeout,
                    'ignore_errors' => true
                ]
            ]);

            $response = @file_get_contents($goApiUrl . '/api/health', false, $context);
            $available = $response !== false;

            // Cache the result
            $lastCheck = $now;
            $lastResult = $available;

            Log::debug('Go API Health Check', [
                'url' => $goApiUrl . '/api/health',
                'available' => $available,
                'cached_until' => $now + $checkInterval
            ]);

            return $available;

        } catch (\Exception $e) {
            Log::warning('Go API Health Check Failed', [
                'error' => $e->getMessage(),
                'url' => ($goApiUrl ?? 'unknown') . '/api/health'
            ]);

            // Cache negative result
            $lastCheck = $now;
            $lastResult = false;

            return false;
        }
    }
}