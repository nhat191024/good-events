<?php

namespace App\Jobs;

use App\Events\SendMessage as SendMessageEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendMessage implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $message)
    {
        $this->onQueue('messages');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        event(new SendMessageEvent($this->message));
    }
}
