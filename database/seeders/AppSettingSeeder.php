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
        $settings->app_title = 'Nền tảng tổ chức sự kiện tốt nhất Việt Nam';
        $settings->app_description = 'Nền tảng tổ chức sự kiện hàng đầu Việt Nam';
        $settings->app_name = 'Sự Kiện Tốt';
        $settings->app_logo = 'images/logo.svg';
        $settings->app_favicon = 'images/favicon.ico';
        $settings->app_partner_title = 'Đối tác của chúng tôi';
        $settings->app_design_title = 'Dịch vụ thiết kế sự kiện';
        $settings->app_rental_title = 'Dịch vụ cho thuê thiết bị sự kiện';
        $settings->contact_hotline = '1800 1234';
        $settings->contact_email = 'contact@sukientot.com';
        $settings->social_facebook = 'https://www.facebook.com/sukientott';
        $settings->social_facebook_group = 'https://www.facebook.com/';
        $settings->social_zalo = 'https://zalo.me/sukientott';
        $settings->social_youtube = 'https://www.youtube.com/sukientott';
        $settings->social_tiktok = 'https://www.tiktok.com/@sukientott';
        $settings->save();
    }
}
