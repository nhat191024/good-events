<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CallResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'thread_id' => $this->thread_id,
            'type' => $this->type,
            'status' => $this->status,
            'initiator' => [
                'id' => $this->initiator->id,
                'name' => $this->initiator->name,
                'avatar' => $this->initiator->avatar_url,
            ],
            'invited_users' => $this->invites->map(fn ($invite): array => [
                'id' => $invite->user->id,
                'name' => $invite->user->name,
                'avatar' => $invite->user->avatar_url,
                'status' => $invite->status,
            ])->values(),
            'participants' => $this->participants
                ->whereNull('left_at')
                ->map(fn ($participant): array => [
                    'id' => $participant->user->id,
                    'name' => $participant->user->name,
                    'avatar' => $participant->user->avatar_url,
                    'joined_at' => $participant->joined_at?->toIso8601String(),
                ])->values(),
            'started_at' => $this->started_at?->toIso8601String(),
            'ended_at' => $this->ended_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
        ];
    }
}
