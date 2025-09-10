<?php

namespace Database\Seeders;

use App\Models\User;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Enum\Role;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->withRole(Role::PARTNER)
            ->createPartner()
            ->create([
                'name' => 'Partner User',
                'email' => 'partner@example.com',
            ]);

        User::factory()
            ->withRole(Role::PARTNER)
            ->count(10)
            ->createPartner()
            ->create();
    }
}
