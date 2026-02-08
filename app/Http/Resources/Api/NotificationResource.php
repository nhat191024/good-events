<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

/** @mixin \Illuminate\Notifications\DatabaseNotification */
class NotificationResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $data = is_array($this->data) ? $this->data : (array) ($this->data ?? []);

        $title = $data['title'] ?? $data['subject'] ?? 'Notification';
        $message = $data['message'] ?? $data['body'] ?? $title;

        $actionUrl = null;
        if (isset($data['actions']) && is_array($data['actions'])) {
            foreach ($data['actions'] as $action) {
                if (!empty($action['url'])) {
                    $actionUrl = $action['url'];
                    break;
                }
            }
        }

        return [
            'id' => (string) $this->id,
            'title' => $title,
            'message' => $message,
            'unread' => is_null($this->read_at),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'href' => $actionUrl,
            'payload' => $data,
        ];
    }
}
