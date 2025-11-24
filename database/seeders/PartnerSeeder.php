<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Partner;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Enum\Role;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $partners = User::factory()
            ->count(10)
            ->createPartner()
            ->create();

        foreach ($partners as $partner) {
            $partner->assignRole(Role::PARTNER);

            DB::table('model_has_roles')
                ->where('model_id', $partner->id)
                ->where('model_type', User::class)
                ->update(['model_type' => Partner::class]);
        }
    }
}
