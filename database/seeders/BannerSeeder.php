<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Banner;

use App\Enum\BannerType;

class BannerSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (BannerType::cases() as $type) {
            Banner::firstOrCreate([
                'type' => $type->value,
            ]);
        }
    }
}
