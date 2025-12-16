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
    protected $signature = 'app:sync-viet-nam-location {--export : Export location data to JSON file}';

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

        if ($this->option('export')) {
            return $this->exportToJson($locations);
        }

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

    /**
     * Export location data to JSON file.
     */
    protected function exportToJson(array $locations): int
    {
        $dataPath = database_path('data');

        // Create directory if it doesn't exist
        if (!file_exists($dataPath)) {
            mkdir($dataPath, 0755, true);
        }

        $filePath = $dataPath . '/vietnam_locations.json';

        $exportData = [
            'exported_at' => now()->toIso8601String(),
            'total_provinces' => count($locations),
            'locations' => $locations,
        ];

        file_put_contents(
            $filePath,
            json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $this->info('Location data exported successfully to: ' . $filePath);
        $this->info('Total provinces: ' . count($locations));

        return Command::SUCCESS;
    }
}
