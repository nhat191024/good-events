<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('app.app_name', 'SukienTot');
        $this->migrator->add('app.app_logo', null);
        $this->migrator->add('app.app_favicon', null);
    }
};
