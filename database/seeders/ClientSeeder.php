<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Customer;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Enum\Role;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = User::factory()
            ->count(10)
            ->create();

        foreach ($clients as $client) {
            $client->assignRole(Role::CLIENT);

            DB::table('model_has_roles')
                ->where('model_id', $client->id)
                ->where('model_type', User::class)
                ->update(['model_type' => Customer::class]);
        }
    }
}
