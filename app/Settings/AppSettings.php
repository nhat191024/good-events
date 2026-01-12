<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AppSettings extends Settings
{
    public string $app_name;
    public ?string $app_logo = null;
    public ?string $app_favicon = null;
    public ?string $app_partner_title = null;
    public ?string $app_design_title = null;
    public ?string $app_rental_title = null;
    public ?string $contact_hotline = null;
    public ?string $contact_email = null;
    public ?string $social_facebook = null;
    public ?string $social_facebook_group = null;
    public ?string $social_zalo = null;
    public ?string $social_youtube = null;
    public ?string $social_tiktok = null;

    public static function group(): string
    {
        return 'app';
    }
}
