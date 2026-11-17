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

    /**
     * The configured VAT percentage, treating an unset value as no VAT.
     */
    public function rate(): int
    {
        return max((int) $this->percentage, 0);
    }

    /**
     * VAT owed on the given amount, rounded to whole currency units.
     */
    public function calculate(float $amount): float
    {
        return round($amount * ($this->rate() / 100));
    }
}
