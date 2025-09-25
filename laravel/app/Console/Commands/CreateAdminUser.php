<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\AuthController;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create default admin user for the system';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $authController = new AuthController();
        $result = $authController->createDefaultAdmin();
        
        $this->info($result);
        
        if (strpos($result, 'created successfully') !== false) {
            $this->line('');
            $this->line('Default admin credentials:');
            $this->line('Email: admin@igd.com');
            $this->line('Password: admin123');
            $this->line('');
            $this->warn('Please change the default password after first login!');
        }
        
        return Command::SUCCESS;
    }
}