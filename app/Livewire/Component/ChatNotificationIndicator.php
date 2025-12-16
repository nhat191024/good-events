<?php

namespace App\Livewire\Component;

use App\Models\Thread;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ChatNotificationIndicator extends Component
{
    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->loadUnreadCount();
    }

    public function loadUnreadCount(): void
    {
        $userId = Auth::id();

        if ($userId === null) {
            $this->unreadCount = 0;

            return;
        }

        $this->unreadCount = Thread::forUser($userId)
            ->whereHas('participants', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where(function ($q) {
                        $q->whereNull('last_read')
                            ->orWhereRaw('threads.updated_at > participants.last_read');
                    });
            })
            ->count();
    }

    #[On('chat:message-received')]
    public function handleNewMessage(): void
    {
        $this->loadUnreadCount();
    }

    public function render(): View
    {
        return view('livewire.chat-notification-indicator');
    }
}
