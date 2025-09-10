<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\PartnerCategory;

class PartnerCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('data/partnerCategories.json')), true);
        foreach ($data as $item) {
            PartnerCategory::create($item);
        }
    }
}
