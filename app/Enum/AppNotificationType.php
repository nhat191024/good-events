<?php

namespace App\Enum;

enum AppNotificationType: string
{
    case ImageOnly = 'image_only';
    case TextAndImage = 'text_and_image';

    public function label(): string
    {
        return match ($this) {
            self::ImageOnly => __('admin/setting.notifications.types.image_only'),
            self::TextAndImage => __('admin/setting.notifications.types.text_and_image'),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->all();
    }
}
