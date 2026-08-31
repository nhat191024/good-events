<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PayOSSettings extends Settings
{
    public ?string $webhook_url = null;

    public static function group(): string
    {
        return 'payos';
    }
}
