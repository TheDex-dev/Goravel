<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoApiService;
use Illuminate\Support\Facades\Http;

class TestGoApiIntegration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:go-api {--endpoint=health : Which endpoint to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Go API integration and connectivity';

    protected $goApiService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(GoApiService $goApiService)
    {
        parent::__construct();
        $this->goApiService = $goApiService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Testing Go API Integration');
        $this->info('================================');

        $endpoint = $this->option('endpoint');

        switch ($endpoint) {
            case 'health':
                return $this->testHealthCheck();
            case 'escorts':
                return $this->testEscortsEndpoint();
            case 'qr':
                return $this->testQrCodeEndpoint();
            case 'all':
                $this->testHealthCheck();
                $this->testEscortsEndpoint();
                $this->testQrCodeEndpoint();
                return 0;
            default:
                return $this->testHealthCheck();
        }
    }

    private function testHealthCheck()
    {
        $this->info('🔍 Testing Go API Health Check...');

        try {
            $result = $this->goApiService->healthCheck();
            
            $this->info('✅ Health Check Passed');
            $this->line("Status: {$result['status']}");
            if (isset($result['message'])) {
                $this->line("Message: {$result['message']}");
            }
            
            if (isset($result['data'])) {
                $this->table(['Property', 'Value'], collect($result['data'])->map(function ($value, $key) {
                    return [$key, is_array($value) ? json_encode($value) : $value];
                })->toArray());
            } else {
                // Display other fields from health check response
                $healthData = [];
                foreach ($result as $key => $value) {
                    if ($key !== 'status' && $key !== 'message') {
                        $healthData[] = [$key, is_array($value) ? json_encode($value) : $value];
                    }
                }
                if (!empty($healthData)) {
                    $this->table(['Property', 'Value'], $healthData);
                }
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Health Check Failed');
            $this->error("Error: {$e->getMessage()}");
            
            $this->warn('💡 Make sure the Go server is running:');
            $this->line('   cd /home/stolas/project0/goserver');
            $this->line('   go build');
            $this->line('   ./goserver');

            return 1;
        }
    }

    private function testEscortsEndpoint()
    {
        $this->info('📋 Testing Escorts Endpoint...');

        try {
            // Test GET escorts
            $result = $this->goApiService->getEscorts(['per_page' => 5]);
            
            $this->info('✅ Get Escorts Passed');
            $this->line("Status: {$result['status']}");
            if (isset($result['message'])) {
                $this->line("Message: {$result['message']}");
            }
            
            if (isset($result['meta'])) {
                $meta = $result['meta'];
                $this->line("Total Records: {$meta['total']}");
                $this->line("Current Page: {$meta['current_page']}");
                $this->line("Per Page: {$meta['per_page']}");
            }

            // Test dashboard stats
            $this->info('📊 Testing Dashboard Stats...');
            $statsResult = $this->goApiService->getDashboardStats();
            
            $this->info('✅ Dashboard Stats Passed');
            if (isset($statsResult['data'])) {
                $stats = $statsResult['data'];
                $this->table(['Metric', 'Count'], [
                    ['Total Escorts', $stats['total_escorts'] ?? 0],
                    ['Today Submissions', $stats['today_submissions'] ?? 0],
                    ['Pending Count', $stats['pending_count'] ?? 0],
                    ['Verified Count', $stats['verified_count'] ?? 0],
                    ['Rejected Count', $stats['rejected_count'] ?? 0],
                ]);
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Escorts Endpoint Failed');
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }
    }

    private function testQrCodeEndpoint()
    {
        $this->info('🔳 Testing QR Code Endpoint...');

        try {
            // Test QR code JSON generation
            $result = $this->goApiService->generateQrCodeJson([
                'url' => 'https://example.com/test',
                'size' => 200
            ]);
            
            $this->info('✅ QR Code JSON Generation Passed');
            $this->line("Status: {$result['status']}");
            if (isset($result['message'])) {
                $this->line("Message: {$result['message']}");
            }
            
            if (isset($result['data']['qr_code_base64'])) {
                $base64Length = strlen($result['data']['qr_code_base64']);
                $this->line("QR Code Base64 Length: {$base64Length} characters");
            }

            // Test QR code PNG generation (just check if we get binary data)
            $pngData = $this->goApiService->generateQrCodePng([
                'url' => 'https://example.com/test',
                'size' => 200
            ]);
            
            $this->info('✅ QR Code PNG Generation Passed');
            $this->line("PNG Data Length: " . strlen($pngData) . " bytes");

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ QR Code Endpoint Failed');
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }
    }
}