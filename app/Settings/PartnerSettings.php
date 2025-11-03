<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PartnerSettings extends Settings
{
    public int $minimum_balance;
    public int $fee_percentage;

    public static function group(): string
    {
        return 'partner';
    }
}
