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

        $this->call([
            RoleSeeder::class,
            // CategorySeeder::class,
            PartnerCategorySeeder::class,
            // LocationSeeder::class,
            EventSeeder::class,
            TestAccountSeeder::class,
            AdminSeeder::class,
            PartnerSeeder::class,
            ClientSeeder::class,
        ]);
    }
}
