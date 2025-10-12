<?php

namespace App\Filament\Partner\Pages;

use App\Events\SendMessage;

use Carbon\Carbon;
use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Models\Participant;

use App\Models\Thread;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Livewire\Attributes\On;

use BackedEnum;

class Chat extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected string $view = 'filament.partner.pages.chat';

    protected static ?string $navigationLabel = 'Chat';

    protected static ?string $title = 'Tin nhắn';

    //threads list & thread per page
    public $threads = [];

    public int $threadsPerPage = 10;

    public int $currentPage = 1;

    public bool $hasMoreThreads = true;

    //selected thread
    public ?int $selectedThreadId = null;

    public ?object $selectedThread = null;

    //selected thread messages
    public $messages = [];

    //user input message
    public string $messageBody = '';


    //show thread list on mobile
    public bool $showThreadListOnMobile = true;

    public $cachedSelectedThread = null;

    public $cachedMessages = null;

    public string $searchTerm = '';

    public function mount(): void
    {
        $this->loadThreads();
    }

    public function updatedSearchTerm(): void
    {
        $this->currentPage = 1;
        $this->hasMoreThreads = true;
        $this->threads = [];
        $this->loadThreads();

        // Dispatch event to update thread subscriptions
        $threadIds = collect($this->threads)->pluck('id')->toArray();
        $this->dispatch('filament-partner-chat:threads-updated', threadIds: $threadIds);
    }

    /**
     * Load more threads when user scrolls to the bottom
     */
    public function loadMoreThreads(): void
    {
        if (!$this->hasMoreThreads) {
            return;
        }

        $this->currentPage++;
        $this->loadThreads(true);
    }

    /**
     * Load threads for the authenticated user
     *
     * @param bool $append Whether to append to existing threads or replace them
     */
    private function loadThreads(bool $append = false): void
    {
        $userId = Auth::id();

        if ($userId === null) {
            if (! $append) {
                $this->threads = [];
            }

            $this->hasMoreThreads = false;

            return;
        }

        $query = Thread::forUserOrderByNotReadMessages($userId)
            ->with(
                [
                    'latestMessage',
                    'participants',
                    'participants.user' => function ($query) {
                        $query->select('id', 'name');
                    },
                ]
            )
            ->latest('updated_at');

        if (!empty(trim($this->searchTerm))) {
            $query->where(function ($q) {
                $q->where('subject', 'like', '%' . $this->searchTerm . '%')
                    ->orWhereHas('participants.user', function ($userQuery) {
                        $userQuery->where('name', 'like', '%' . $this->searchTerm . '%');
                    });
            });
        }

        $threads = $query
            ->skip(($this->currentPage - 1) * $this->threadsPerPage)
            ->take($this->threadsPerPage + 1)
            ->get();

        //check if there are more threads to load
        $this->hasMoreThreads = $threads->count() > $this->threadsPerPage;

        //if there are more threads, remove the last one
        if ($this->hasMoreThreads) {
            $threads = $threads->take($this->threadsPerPage);
        }

        $mappedThreads = $threads->map(function ($thread) use ($userId) {
            $isUnread = false;
            $participant = $thread->participants->firstWhere('user_id', $userId);

            if ($participant) {
                $isUnread = $participant->last_read === null || $thread->updated_at->gt($participant->last_read);
            }

            return (object) [
                'id' => $thread->id,
                'subject' => $thread->subject,
                'updated_at' => $thread->updated_at,
                'is_unread' => $isUnread,
                'other_participants' => $thread->participants->where('user_id', '!=', $userId)->map(function ($participant) {
                    return (object) [
                        'id' => $participant->user->id,
                        'name' => $participant->user->name,
                    ];
                }),
                'participants' => $thread->participants->map(function ($participant) {
                    return (object) [
                        'id' => $participant->user->id,
                        'name' => $participant->user->name,
                    ];
                }),
                'latest_message' => $thread->latestMessage ? (object) [
                    'body' => $thread->latestMessage->body,
                    'created_at' => $thread->latestMessage->created_at->toIso8601String(),
                ] : null,
            ];
        });

        if ($append) {
            $this->threads = collect($this->threads)->merge($mappedThreads)->all();
        } else {
            $this->threads = $mappedThreads->all();
        }
    }

    public function showThreadList(): void
    {
        $this->showThreadListOnMobile = true;
    }

    /**
     * Open thread and clear unread count
     *
     * @param int $threadId
     */
    public function openThread(int $threadId)
    {
        $oldThreadId = $this->selectedThreadId;
        $this->selectedThreadId = $threadId;

        //clear user input message
        $this->messageBody = '';

        //get thread data
        if (!$this->selectedThreadId) {
            return null;
        }

        if ($this->cachedSelectedThread && $this->cachedSelectedThread->id === $this->selectedThreadId) {
            $this->selectedThread = $this->cachedSelectedThread;
        } else {
            $this->cachedSelectedThread = null;

            $threads = collect($this->threads);
            $thread = $threads->firstWhere('id', $this->selectedThreadId);
            $thread->is_unread = false;
            $this->selectedThread = $thread;
            $this->cachedSelectedThread = $thread;
        }


        if ($this->cachedMessages && $oldThreadId === $threadId) {
            $this->messages = $this->cachedMessages;
        } else {
            $this->cachedMessages = null;

            $message = Thread::with([
                'messages.user' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
                ->find($threadId);

            $this->messages = $message->messages->map(fn($msg) => [
                'id' => $msg->id,
                'thread_id' => $msg->thread_id,
                'user_id' => $msg->user_id,
                'body' => $msg->body,
                'created_at' => $msg->created_at,
                'updated_at' => $msg->updated_at,
                'user' => [
                    'id' => $msg->user->id,
                    'name' => $msg->user->name,
                ],
            ])->toArray();

            $this->cachedMessages = $this->messages;

            $message->markAsRead(Auth::id());
        }

        $this->showThreadListOnMobile = false;
    }

    /**
     * Send message to the selected thread
     */
    public function sendMessage(): void
    {
        if (empty(trim($this->messageBody)) || !$this->selectedThreadId) {
            return;
        }

        $userId = Auth::id();
        $threadId = $this->selectedThreadId;

        try {
            $message = Message::create([
                'thread_id' => $threadId,
                'user_id' => $userId,
                'body' => $this->messageBody,
            ]);
        } catch (QueryException $exception) {
            report($exception);

            return;
        }

        $message = [
            'id' => $message->id,
            'thread_id' => $message->thread_id,
            'user_id' => $message->user_id,
            'body' => $message->body,
            'created_at' => $message->created_at,
            'updated_at' => $message->updated_at,
            'user' => [
                'id' => $userId,
                'name' => Auth::user() ? Auth::user()->name : 'Người dùng đã xóa',
            ],
        ];

        $this->messages = collect($this->messages)->push($message);
        $this->cachedMessages = $this->messages;

        $participant = Participant::firstOrCreate([
            'thread_id' => $threadId,
            'user_id' => $userId,
        ]);
        $participant->last_read = now();
        $participant->save();

        // Clear message input
        $this->messageBody = '';

        // Broadcast the new message
        event(new SendMessage($message));
    }


    /**
     * Handle incoming broadcasted messages
     *
     * @param array|null $payload
     */
    #[On('chat:message-received')]
    public function handleBroadcastMessage($payload = null): void
    {
        if (!$payload) {
            return;
        }

        $threadId = (int) data_get($payload, 'message.thread_id');
        $senderId = (int) data_get($payload, 'sender_id');

        if ($threadId === 0 || $senderId === Auth::id()) {
            return;
        }

        $messageData = data_get($payload, 'message');
        $userData = data_get($payload, 'user');

        if (!is_array($messageData) || !is_array($userData)) {
            return;
        }

        // Add new message to the list if viewing this thread
        if ($threadId === $this->selectedThreadId) {
            $newMessage = [
                'id' => $messageData['id'],
                'thread_id' => $messageData['thread_id'],
                'user_id' => $messageData['user_id'],
                'body' => $messageData['body'],
                'created_at' => Carbon::parse($messageData['created_at']),
                'updated_at' => Carbon::parse($messageData['updated_at']),
                'user' => [
                    'id' => $userData['id'],
                    'name' => $userData['name'],
                ],
            ];

            $this->messages = collect($this->messages)->push($newMessage)->toArray();
            $this->cachedMessages = $this->messages;

            Thread::find($threadId)?->markAsRead(Auth::id());
        }

        $this->updateThreadInList($threadId, $messageData);
    }

    /**
     * Update thread in the list with the latest message
     *
     * @param int $threadId
     * @param array $messageData
     * @return void
     */
    private function updateThreadInList(int $threadId, array $messageData): void
    {
        $threads = collect($this->threads);

        if ($threads->isEmpty()) {
            return;
        }

        $index = $threads->search(fn($thread) => $thread->id === $threadId);

        if ($index === false) {
            return;
        }

        $thread = clone $threads[$index];

        $createdAt = Carbon::parse($messageData['created_at']);

        $thread->latest_message = (object) [
            'body' => $messageData['body'],
            'created_at' => $createdAt->toIso8601String(),
        ];
        $thread->updated_at = $createdAt;

        // Mark as unread if not currently viewing this thread
        if ($threadId !== $this->selectedThreadId) {
            $thread->is_unread = true;
        }

        $threads->forget($index);
        $threads->prepend($thread);

        $this->threads = $threads->values()->all();
    }
}
