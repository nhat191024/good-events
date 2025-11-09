<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Location;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('data/vietnam_locations.json')), true);

        foreach ($data['locations'] as $location) {
            $model = Location::firstOrCreate(
                [
                    'name' => $location['name'],
                    'code' => $location['code'],
                    'codename' => $location['codename'],
                    'type' => $location['division_type'],
                    'phone_code' => $location['phone_code'],
                ]
            );

            foreach ($location['wards'] as $ward) {
                $model->wards()->firstOrCreate(
                    [
                        'name' => $ward['name'],
                        'code' => $ward['code'],
                        'codename' => $ward['codename'],
                        'type' => $ward['division_type'],
                        'short_codename' => $ward['short_codename'],
                    ]
                );
            }
        }
    }
}
