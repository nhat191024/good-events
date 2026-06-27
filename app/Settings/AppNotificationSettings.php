<?php

namespace App\Settings;

use App\Enum\AppNotificationType;
use Spatie\LaravelSettings\Settings;

class AppNotificationSettings extends Settings
{
    public string $partner_type = AppNotificationType::ImageOnly->value;
    public ?string $partner_notification_image = null;
    public ?string $partner_title = null;
    public ?string $partner_content = null;
    public ?string $partner_image = null;

    public string $customer_type = AppNotificationType::ImageOnly->value;
    public ?string $customer_notification_image = null;
    public ?string $customer_title = null;
    public ?string $customer_content = null;
    public ?string $customer_image = null;

    public static function group(): string
    {
        return 'app_notification';
    }
}
