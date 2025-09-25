<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {        // Seed users. The count can be set via USER_SEED_COUNT in your .env (default 500).
        $this->call([
            UserSeeder::class,
            EscortSeeder::class,
        ]);
    }
}
