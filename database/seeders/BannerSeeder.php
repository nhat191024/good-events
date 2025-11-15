<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Banner;

class BannerSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bannerTypes = ['partner', 'design', 'rental'];
        foreach ($bannerTypes as $type) {
            Banner::create([
                'type' => $type,
            ]);
        }
    }
}
