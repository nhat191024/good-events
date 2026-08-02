<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @param array<string, mixed> $call */
    public function __construct(public readonly array $call) {}

    public function broadcastAs(): string
    {
        return 'CallUpdated';
    }

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('thread.'.$this->call['thread_id'])];
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ['call' => $this->call];
    }
}
