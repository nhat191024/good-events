<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Settings\AppSettings;

class AppSettingSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //spatie app settings seeder example
        $settings = app(AppSettings::class);
        $settings->app_name = 'Sự Kiện Tốt';
        $settings->app_logo = 'images/logo.png';
        $settings->app_favicon = 'images/favicon.ico';
        $settings->save();
    }
}
