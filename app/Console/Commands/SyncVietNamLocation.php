<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Location;

class SyncVietNamLocation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-viet-nam-location';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Viet Nam Location';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //Api use: https://provinces.open-api.vn/api/v2?depth=2
        //note depth=2 for province and ward
        //v2 for new vietnam location from 07/2025

        $response = Http::withoutVerifying()
            ->get('https://provinces.open-api.vn/api/v2', ['depth' => 2]);

        if ($response->failed()) {
            $this->error('Failed to fetch location data from API.');
            return Command::FAILURE;
        }

        $locations = $response->json();

        //data example
        // {
        //     "name": "Cao Bằng",
        //     "code": 4,
        //     "codename": "cao_bang",
        //     "division_type": "tỉnh",
        //     "phone_code": 206,
        //     "wards": [
        //         {
        //             "name": "Phường Thục Phán",
        //             "code": 1273,
        //             "codename": "phuong_thuc_phan",
        //             "division_type": "phường",
        //             "short_codename": "thuc_phan"
        //         }
        //     ]
        // }

        foreach ($locations as $location) {
            $this->info('Processing location: ' . $location['name']);

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

        $this->info('Viet Nam locations synced successfully.');
        return Command::SUCCESS;
    }
}
