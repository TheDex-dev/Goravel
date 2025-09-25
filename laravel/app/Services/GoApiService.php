<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class GoApiService
{
    private $client;
    private $baseUrl;

    public function __construct()
    {
        // Get Go API URL from config or environment
        $this->baseUrl = config('services.go_api.url', env('GO_API_URL', 'http://localhost:8080'));
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    /**
     * Get all escorts with optional filters
     *
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getEscorts(array $params = []): array
    {
        try {
            $response = $this->client->get('/api/escort', [
                'query' => $params
            ]);

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            Log::error('Go API getEscorts failed', [
                'error' => $e->getMessage(),
                'params' => $params
            ]);
            throw new \Exception('Failed to retrieve escorts from Go API: ' . $e->getMessage());
        }
    }

    /**
     * Create a new escort
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function createEscort(array $data): array
    {
        try {
            $response = $this->client->post('/api/escort', [
                'json' => $data
            ]);

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            Log::error('Go API createEscort failed', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw new \Exception('Failed to create escort in Go API: ' . $e->getMessage());
        }
    }

    /**
     * Get a single escort by ID
     *
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getEscort(int $id): array
    {
        try {
            $response = $this->client->get("/api/escort/{$id}");

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            Log::error('Go API getEscort failed', [
                'error' => $e->getMessage(),
                'escort_id' => $id
            ]);
            
            // Handle 404 specifically
            if (method_exists($e, 'getResponse') && $e->getResponse() && $e->getResponse()->getStatusCode() === 404) {
                throw new \Exception('Escort not found', 404);
            }
            
            throw new \Exception('Failed to retrieve escort from Go API: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing escort
     *
     * @param int $id
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function updateEscort(int $id, array $data): array
    {
        try {
            $response = $this->client->put("/api/escort/{$id}", [
                'json' => $data
            ]);

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            Log::error('Go API updateEscort failed', [
                'error' => $e->getMessage(),
                'escort_id' => $id,
                'data' => $data
            ]);
            
            // Handle 404 specifically
            if (method_exists($e, 'getResponse') && $e->getResponse() && $e->getResponse()->getStatusCode() === 404) {
                throw new \Exception('Escort not found', 404);
            }
            
            throw new \Exception('Failed to update escort in Go API: ' . $e->getMessage());
        }
    }

    /**
     * Delete an escort
     *
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function deleteEscort(int $id): array
    {
        try {
            $response = $this->client->delete("/api/escort/{$id}");

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            Log::error('Go API deleteEscort failed', [
                'error' => $e->getMessage(),
                'escort_id' => $id
            ]);
            
            // Handle 404 specifically
            if (method_exists($e, 'getResponse') && $e->getResponse() && $e->getResponse()->getStatusCode() === 404) {
                throw new \Exception('Escort not found', 404);
            }
            
            throw new \Exception('Failed to delete escort in Go API: ' . $e->getMessage());
        }
    }

    /**
     * Update escort status
     *
     * @param int $id
     * @param string $status
     * @return array
     * @throws \Exception
     */
    public function updateEscortStatus(int $id, string $status): array
    {
        try {
            $response = $this->client->patch("/api/escort/{$id}/status", [
                'json' => ['status' => $status]
            ]);

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            Log::error('Go API updateEscortStatus failed', [
                'error' => $e->getMessage(),
                'escort_id' => $id,
                'status' => $status
            ]);
            
            // Handle 404 specifically
            if (method_exists($e, 'getResponse') && $e->getResponse() && $e->getResponse()->getStatusCode() === 404) {
                throw new \Exception('Escort not found', 404);
            }
            
            throw new \Exception('Failed to update escort status in Go API: ' . $e->getMessage());
        }
    }

    /**
     * Get dashboard statistics
     *
     * @return array
     * @throws \Exception
     */
    public function getDashboardStats(): array
    {
        try {
            $response = $this->client->get('/api/dashboard/stats');

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            Log::error('Go API getDashboardStats failed', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to retrieve dashboard stats from Go API: ' . $e->getMessage());
        }
    }

    /**
     * Get image as base64
     *
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getImageBase64(int $id): array
    {
        try {
            $response = $this->client->get("/api/escort/{$id}/image/base64");

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            Log::error('Go API getImageBase64 failed', [
                'error' => $e->getMessage(),
                'escort_id' => $id
            ]);
            
            // Handle 404 specifically
            if (method_exists($e, 'getResponse') && $e->getResponse() && $e->getResponse()->getStatusCode() === 404) {
                throw new \Exception('Image not found', 404);
            }
            
            throw new \Exception('Failed to retrieve image from Go API: ' . $e->getMessage());
        }
    }

    /**
     * Upload image as base64
     *
     * @param int $id
     * @param array $imageData
     * @return array
     * @throws \Exception
     */
    public function uploadImageBase64(int $id, array $imageData): array
    {
        try {
            $response = $this->client->post("/api/escort/{$id}/image/base64", [
                'json' => $imageData
            ]);

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            Log::error('Go API uploadImageBase64 failed', [
                'error' => $e->getMessage(),
                'escort_id' => $id
            ]);
            
            // Handle 404 specifically
            if (method_exists($e, 'getResponse') && $e->getResponse() && $e->getResponse()->getStatusCode() === 404) {
                throw new \Exception('Escort not found', 404);
            }
            
            throw new \Exception('Failed to upload image to Go API: ' . $e->getMessage());
        }
    }

    /**
     * Generate QR code (GET method - returns PNG)
     *
     * @param array $params
     * @return string Binary image data
     * @throws \Exception
     */
    public function generateQrCodePng(array $params = []): string
    {
        try {
            $response = $this->client->get('/api/qr-code/form', [
                'query' => $params
            ]);

            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            Log::error('Go API generateQrCodePng failed', [
                'error' => $e->getMessage(),
                'params' => $params
            ]);
            throw new \Exception('Failed to generate QR code PNG from Go API: ' . $e->getMessage());
        }
    }

    /**
     * Generate QR code (POST method - returns JSON with base64)
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function generateQrCodeJson(array $data): array
    {
        try {
            $response = $this->client->post('/api/qr-code/form', [
                'json' => $data
            ]);

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            Log::error('Go API generateQrCodeJson failed', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw new \Exception('Failed to generate QR code JSON from Go API: ' . $e->getMessage());
        }
    }

    /**
     * Get session statistics
     *
     * @return array
     * @throws \Exception
     */
    public function getSessionStats(): array
    {
        try {
            $response = $this->client->get('/api/session-stats');

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            Log::error('Go API getSessionStats failed', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to retrieve session stats from Go API: ' . $e->getMessage());
        }
    }

    /**
     * Health check endpoint
     *
     * @return array
     * @throws \Exception
     */
    public function healthCheck(): array
    {
        try {
            $response = $this->client->get('/api/health');

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            Log::error('Go API healthCheck failed', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Go API health check failed: ' . $e->getMessage());
        }
    }

    /**
     * Database test endpoint
     *
     * @return array
     * @throws \Exception
     */
    public function databaseTest(): array
    {
        try {
            $response = $this->client->get('/api/db-test');

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            Log::error('Go API databaseTest failed', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Go API database test failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle HTTP response from Go API
     *
     * @param $response
     * @return array
     * @throws \Exception
     */
    private function handleResponse($response): array
    {
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        
        $data = json_decode($body, true);
        
        if ($data === null) {
            throw new \Exception('Invalid JSON response from Go API');
        }
        
        if ($statusCode >= 200 && $statusCode < 300) {
            return $data;
        } else {
            // Handle error response - check for various error message fields
            $errorMessage = $data['message'] ?? $data['error'] ?? $data['details'] ?? 'Unknown error from Go API';
            throw new \Exception($errorMessage, $statusCode);
        }
    }

    /**
     * Get the Go API base URL
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}