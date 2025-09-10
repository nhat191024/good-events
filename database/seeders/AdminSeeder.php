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
        User::factory()->withRole(Role::ADMIN)->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
    }
}
