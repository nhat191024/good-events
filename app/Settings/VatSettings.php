<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class VatSettings extends Settings
{
    public ?int $percentage = null;

    public static function group(): string
    {
        return 'vat';
    }
}
