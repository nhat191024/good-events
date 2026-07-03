<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('app_notification.partner_enabled', false);
        $this->migrator->add('app_notification.customer_enabled', false);
    }
};
