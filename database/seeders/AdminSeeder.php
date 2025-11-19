<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;

use App\Enum\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => env('SUPER_ADMIN_PASSWORD'),
        ]);

        User::factory()->withRole(Role::ADMIN)->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => env('ADMIN_PASSWORD'),
        ]);
    }
}
