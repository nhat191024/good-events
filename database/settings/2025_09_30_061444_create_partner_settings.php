<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('partner.minimum_balance', 200000);
        $this->migrator->add('partner.fee_percentage', 5);
        $this->migrator->add('partner.default_balance', 0);
    }
};
