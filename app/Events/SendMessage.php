<?php

namespace App\Events;

use App\Support\ChatMessagePayload;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'SendMessage';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('thread.' . $this->message['thread_id'])];

        foreach ($this->message['other_participant_ids'] ?? [] as $participantId) {
            $channels[] = new PrivateChannel('user-messages.' . $participantId);
        }

        return $channels;
    }

    /**
     * Get the data to be broadcast with the event.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $payload = ChatMessagePayload::broadcast($this->message);

        Log::info('🚀 Broadcasting message', $payload);

        return $payload;
    }
}
