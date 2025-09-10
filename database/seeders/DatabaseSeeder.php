<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Enum\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
            PartnerCategorySeeder::class,
            PartnerSeeder::class,
        ]);

        // Create admin user with 'admin' role using factory state
        User::factory()->withRole(Role::ADMIN)->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
    }
}
