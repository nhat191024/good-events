<?php

namespace App\Http\Controllers\Api\Common;

use App\Enum\AppNotificationType;
use App\Http\Controllers\Controller;
use App\Settings\AppNotificationSettings;
use App\Settings\AppSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Get application settings for the client app.
     */
    public function getSettings(Request $request): JsonResponse
    {
        $appSettings = app(AppSettings::class);
        $appNotificationSettings = app(AppNotificationSettings::class);

        $settings = [
            'hotline' => $appSettings->contact_hotline,
            'zalo' => $appSettings->social_zalo,
            'notifications' => [
                'partner' => $this->formatNotificationSettings(
                    $appNotificationSettings->partner_type,
                    $appNotificationSettings->partner_notification_image,
                    $appNotificationSettings->partner_title,
                    $appNotificationSettings->partner_content,
                    $appNotificationSettings->partner_image,
                ),
                'customer' => $this->formatNotificationSettings(
                    $appNotificationSettings->customer_type,
                    $appNotificationSettings->customer_notification_image,
                    $appNotificationSettings->customer_title,
                    $appNotificationSettings->customer_content,
                    $appNotificationSettings->customer_image,
                ),
            ],
        ];

        return response()->json($settings);
    }

    /**
     * @return array{type: string, notification_image: ?string, title: ?string, content: ?string, image: ?string}
     */
    private function formatNotificationSettings(
        string $type,
        ?string $notificationImage,
        ?string $title,
        ?string $content,
        ?string $image,
    ): array {
        if ($type === AppNotificationType::ImageOnly->value) {
            return [
                'type' => $type,
                'notification_image' => $notificationImage,
                'title' => null,
                'content' => null,
                'image' => null,
            ];
        }

        if ($type === AppNotificationType::TextOnly->value) {
            return [
                'type' => $type,
                'notification_image' => null,
                'title' => $title,
                'content' => $content,
                'image' => null,
            ];
        }

        return [
            'type' => $type,
            'notification_image' => null,
            'title' => $title,
            'content' => $content,
            'image' => $image,
        ];
    }
}
