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

        if (Location::count() == 0) {
            $this->command->info('Location table is empty. Syncing Viet Nam locations...');
            //run location sync command
            Artisan::call('app:sync-viet-nam-location');
        } else {
            $this->command->info('Location table already has data. Skipping location sync.');
        }

        if (env('APP_ENV') === 'production') {
            $this->command->info('Production environment detected. Skipping seeders that may affect production data.');
            $this->call([
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
