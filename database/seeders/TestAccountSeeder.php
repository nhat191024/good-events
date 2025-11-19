<?php

namespace Database\Seeders;

use App\Models\User;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Enum\Role;

class TestAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //move test accounts from other seeders to here for easier management

        User::factory()->withRole(Role::SUPER_ADMIN)->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        User::factory()->withRole(Role::PARTNER)
            ->createPartner()
            ->create([
                'name' => 'Partner User',
                'email' => 'partner@example.com',
            ]);

        User::factory()->withRole(Role::CLIENT)
            ->create([
                'name' => 'Client User',
                'email' => 'client@example.com',
            ]);
    }
}
