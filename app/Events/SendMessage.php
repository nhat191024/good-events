<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('thread.' . $this->message->thread_id),
        ];
    }

    /**
     * Get the data to be broadcast with the event.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $userPayload = null;

        if ($this->message->relationLoaded('user') && $this->message->user) {
            $userPayload = [
                'id' => $this->message->user->id,
                'name' => $this->message->user->name,
                'avatar' => $this->message->user->avatar ?? null,
            ];
        }

        $createdAt = $this->message->created_at ? $this->message->created_at->toIso8601String() : null;
        $updatedAt = $this->message->updated_at ? $this->message->updated_at->toIso8601String() : null;

        return [
            'sender_id' => $this->message->user_id,
            'message' => [
                'id' => $this->message->id,
                'thread_id' => $this->message->thread_id,
                'user_id' => $this->message->user_id,
                'body' => $this->message->body,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ],
            'user' => $userPayload,
        ];
    }
}
