<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Customer;
use App\Models\Partner;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Enum\Role;

class TestAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //move test accounts from other seeders to here for easier management

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole(Role::SUPER_ADMIN);

        $partner = User::factory()->createPartner()->create([
            'name' => 'Partner User',
            'email' => 'partner@example.com',
        ]);
        $partner->assignRole(Role::PARTNER);

        DB::table('model_has_roles')
            ->where('model_id', $partner->id)
            ->where('model_type', User::class)
            ->update(['model_type' => Partner::class]);

        $client = User::factory()->create([
            'name' => 'Client User',
            'email' => 'client@example.com',
        ]);
        $client->assignRole(Role::CLIENT);

        DB::table('model_has_roles')
            ->where('model_id', $client->id)
            ->where('model_type', User::class)
            ->update(['model_type' => Customer::class]);
    }
}
