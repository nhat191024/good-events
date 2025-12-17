<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $data = is_array($this->data) ? $this->data : (array) ($this->data ?? []);

        $title = $data['title'] ?? $data['subject'] ?? 'Thông báo';
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
            'id'         => (string) $this->id,
            'title'      => $title,
            'message'    => $message,
            'unread'     => is_null($this->read_at),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'href'       => $actionUrl,
            'payload'    => $data,
        ];
    }
}
