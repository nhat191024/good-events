<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\Location;

class SyncVietNamLocation implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //Api use: https://provinces.open-api.vn/api/v2?depth=2
        //note depth=2 for province and ward
        //v2 for new vietnam location from 07/2025

        $response = file_get_contents('https://provinces.open-api.vn/api/v2?depth=2');
        $locations = json_decode($response, true);

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
