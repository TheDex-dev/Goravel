<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoApiService;
use Illuminate\Support\Facades\Log;

class TestGoApiConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:go-api-connection {--count=3 : Number of test calls to make}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the Laravel to Go API connection and log the results';

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
        $this->info('🚀 Testing Laravel to Go API Connection...');
        $this->info('📋 Check the Laravel logs for detailed connection tracking!');
        $this->newLine();

        $count = (int) $this->option('count');
        $successCount = 0;
        $failureCount = 0;

        // Log the test session start
        Log::info('=== Go API Connection Test Session Started ===', [
            'command' => 'test:go-api-connection',
            'test_count' => $count,
            'timestamp' => now()->toISOString(),
            'user_agent' => 'Laravel Artisan Command'
        ]);

        for ($i = 1; $i <= $count; $i++) {
            $this->info("Test #{$i}: Testing connection to Go API...");

            try {
                // Test 1: Health Check
                $this->line("  → Testing health endpoint...");
                $healthResult = $this->goApiService->healthCheck();
                $this->info("  ✅ Health check successful!");

                // Test 2: Dashboard Stats
                $this->line("  → Testing dashboard stats endpoint...");
                $statsResult = $this->goApiService->getDashboardStats();
                $this->info("  ✅ Dashboard stats retrieved successfully!");

                // Test 3: Get Escorts (with empty params)
                $this->line("  → Testing escorts list endpoint...");
                $escortsResult = $this->goApiService->getEscorts();
                $this->info("  ✅ Escorts list retrieved successfully!");

                $successCount++;
                $this->info("✅ Test #{$i} completed successfully!");

            } catch (\Exception $e) {
                $failureCount++;
                $this->error("❌ Test #{$i} failed: " . $e->getMessage());
            }

            $this->newLine();
            
            // Add a small delay between tests
            if ($i < $count) {
                sleep(1);
            }
        }

        // Summary
        $this->info("📊 Test Summary:");
        $this->info("  • Total tests: {$count}");
        $this->info("  • Successful: {$successCount}");
        $this->info("  • Failed: {$failureCount}");
        $this->info("  • Success rate: " . round(($successCount / $count) * 100, 1) . "%");

        // Log the test session end
        Log::info('=== Go API Connection Test Session Completed ===', [
            'command' => 'test:go-api-connection',
            'total_tests' => $count,
            'successful_tests' => $successCount,
            'failed_tests' => $failureCount,
            'success_rate_percent' => round(($successCount / $count) * 100, 1),
            'timestamp' => now()->toISOString()
        ]);

        $this->newLine();
        $this->info("📝 Check your Laravel logs for detailed connection information:");
        $this->line("  → tail -f storage/logs/laravel.log | grep 'Laravel->Go API'");
        
        return $successCount === $count ? 0 : 1;
    }
}