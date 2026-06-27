<?php

use App\Enum\AppNotificationType;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('app_notification.partner_type', AppNotificationType::ImageOnly->value);
        $this->migrator->add('app_notification.partner_notification_image', null);
        $this->migrator->add('app_notification.partner_title', null);
        $this->migrator->add('app_notification.partner_content', null);
        $this->migrator->add('app_notification.partner_image', null);

        $this->migrator->add('app_notification.customer_type', AppNotificationType::ImageOnly->value);
        $this->migrator->add('app_notification.customer_notification_image', null);
        $this->migrator->add('app_notification.customer_title', null);
        $this->migrator->add('app_notification.customer_content', null);
        $this->migrator->add('app_notification.customer_image', null);
    }
};
