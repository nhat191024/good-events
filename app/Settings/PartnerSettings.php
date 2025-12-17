<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PartnerSettings extends Settings
{
    public ?int $minimum_balance = null;
    public ?int $fee_percentage = null;
    public ?int $default_balance = null;

    public static function group(): string
    {
        return 'partner';
    }
}
