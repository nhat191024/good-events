<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('app.app_title', 'Nền tảng tổ chức sự kiện tốt nhất Việt Nam');
        $this->migrator->add('app.app_description', 'Nền tảng tổ chức sự kiện hàng đầu Việt Nam');
        $this->migrator->add('app.app_name', 'Sự Kiện Tốt');
        $this->migrator->add('app.app_logo', 'images/logo.svg');
        $this->migrator->add('app.app_favicon', 'images/favicon.ico');
        $this->migrator->add('app.app_partner_title', 'Đối tác của chúng tôi');
        $this->migrator->add('app.app_design_title', 'Dịch vụ thiết kế sự kiện');
        $this->migrator->add('app.app_rental_title', 'Dịch vụ cho thuê thiết bị sự kiện');
        $this->migrator->add('app.contact_hotline', '1800 1234');
        $this->migrator->add('app.contact_email', 'contact@sukientot.com');
        $this->migrator->add('app.social_facebook', 'https://www.facebook.com/sukientott');
        $this->migrator->add('app.social_facebook_group', 'https://www.facebook.com/');
        $this->migrator->add('app.social_zalo', 'https://zalo.me/sukientott');
        $this->migrator->add('app.social_youtube', 'https://www.youtube.com/sukientott');
        $this->migrator->add('app.social_tiktok', 'https://www.tiktok.com/@sukientott');
    }
};
