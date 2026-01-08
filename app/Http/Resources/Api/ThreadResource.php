<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

/** @mixin \App\Models\Thread */
class ThreadResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'latest_message' => $this->whenLoaded('latestMessage', function () {
                $message = $this->latestMessage;

                return [
                    'id' => $message->id,
                    'user_id' => $message->user_id,
                    'body' => $message->body,
                    'created_at' => optional($message->created_at)->toIso8601String(),
                ];
            }),
        ];
    }
}
