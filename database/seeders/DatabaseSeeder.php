<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

use App\Models\Location;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        if (env('APP_ENV') === 'production' || env('APP_ENV') === 'testing') {
            $this->command->info('Production environment detected. Skipping seeders that may affect production data.');
            $this->call([
                LocationSeeder::class,
                RoleSeeder::class,
                AdminSeeder::class,
            ]);
            return;
        }

        //seeders for non-production environment
        $this->call([
            RoleSeeder::class,
            // CategorySeeder::class,
            PartnerCategorySeeder::class,
            // LocationSeeder::class,
            EventSeeder::class,
            TestAccountSeeder::class,
            PartnerSeeder::class,
            ClientSeeder::class,
            // New comprehensive data seeders (order matters)
            StatisticalSeeder::class,
            // PartnerBillSeeder::class,
            // ReviewSeeder::class,
        ]);
    }
}
