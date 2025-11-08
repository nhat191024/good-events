<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AppSettings extends Settings
{
    public string $app_name;
    public ?string $app_logo = null;
    public ?string $app_favicon = null;
    public ?string $app_partner_banner = null;
    public ?string $app_design_banner = null;
    public ?string $app_rental_banner = null;

    public static function group(): string
    {
        return 'app';
    }
}
