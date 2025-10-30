<?php

namespace App\Http\Controllers\Client;

use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

use App\Models\Thread;
use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Models\Participant;

use App\Enum\PartnerBillStatus;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;

/**
 *  @property PartnerBillStatus $status
 */
class ChatController extends Controller
{
    private const int THREADS_PER_PAGE = 10;
    private const int MESSAGES_PER_PAGE = 20;

    public function index(Request $request): Response
    {
        $userId = Auth::id();
        $searchTerm = $request->input('search', '');

        $threads = $this->getThreads($userId, $searchTerm, 1);

        return Inertia::render('chat/ChatPage', [
            'initialThreads' => $threads['data'],
            'hasMoreThreads' => $threads['hasMore'],
        ]);
    }

    public function loadThreads(Request $request)
    {
        $userId = Auth::id();
        $searchTerm = $request->input('search', '');
        $page = $request->input('page', 1);

        $threads = $this->getThreads($userId, $searchTerm, $page);

        return response()->json($threads);
    }

    public function loadMessages(Request $request, int $threadId)
    {
        $page = $request->input('page', 1);
        $messages = $this->getMessages($threadId, $page);

        return response()->json($messages);
    }

    public function sendMessage(Request $request, int $threadId)
    {
        $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $userId = Auth::id();
        $messageBody = $request->input('body');

        try {
            $message = Message::create([
                'thread_id' => $threadId,
                'user_id' => $userId,
                'body' => $messageBody,
            ]);

            $participant = Participant::firstOrCreate([
                'thread_id' => $threadId,
                'user_id' => $userId,
            ]);
            $participant->last_read = now();
            $participant->save();

            $formattedMessage = [
                'id' => $message->id,
                'thread_id' => $message->thread_id,
                'user_id' => $message->user_id,
                'body' => $message->body,
                'created_at' => $message->created_at,
                'updated_at' => $message->updated_at,
                'user' => [
                    'id' => $userId,
                    'name' => Auth::user()?->name ?? 'Người dùng đã xóa',
                ],
            ];

            // Broadcast the new message
            event(new \App\Events\SendMessage($formattedMessage));

            return response()->json([
                'success' => true,
                'message' => $formattedMessage,
            ]);
        } catch (QueryException $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi gửi tin nhắn',
            ], 500);
        }
    }

    private function getThreads(?int $userId, string $searchTerm, int $page): array
    {
        if ($userId === null) {
            return [
                'data' => [],
                'hasMore' => false,
            ];
        }

        $query = Thread::forUserOrderByNotReadMessages($userId)
            ->with([
                'latestMessage',
                'participants',
                'participants.user' => function ($query) {
                    $query->select('id', 'name');
                },
            ])
            ->latest('updated_at');

        if (!empty(trim($searchTerm))) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('subject', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('participants.user', function ($userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        $threads = $query
            ->skip(($page - 1) * self::THREADS_PER_PAGE)
            ->take(self::THREADS_PER_PAGE + 1)
            ->get();

        $hasMore = $threads->count() > self::THREADS_PER_PAGE;

        if ($hasMore) {
            $threads = $threads->take(self::THREADS_PER_PAGE);
        }

        $mappedThreads = $threads->map(function ($thread) use ($userId) {
            $isUnread = false;
            $participant = $thread->participants->firstWhere('user_id', $userId);

            if ($participant) {
                $isUnread = $participant->last_read === null || $thread->updated_at->gt($participant->last_read);
            }

            return [
                'id' => $thread->id,
                'subject' => $thread->subject,
                'updated_at' => $thread->updated_at->toIso8601String(),
                'is_unread' => $isUnread,
                'other_participants' => $thread->participants->where('user_id', '!=', $userId)->map(function ($participant) {
                    return [
                        'id' => $participant->user->id,
                        'name' => $participant->user->name,
                    ];
                })->values(),
                'participants' => $thread->participants->map(function ($participant) {
                    return [
                        'id' => $participant->user->id,
                        'name' => $participant->user->name,
                    ];
                })->values(),
                'latest_message' => $thread->latestMessage ? [
                    'body' => $thread->latestMessage->body,
                    'created_at' => $thread->latestMessage->created_at->toIso8601String(),
                ] : null,
            ];
        });

        return [
            'data' => $mappedThreads->values()->all(),
            'hasMore' => $hasMore,
        ];
    }

    private function getMessages(int $threadId, int $page): array
    {
        $thread = Thread::find($threadId);
        $thread?->markAsRead(Auth::id());

        if (!$thread) {
            return [
                'data' => [],
                'hasMore' => false,
                'thread' => null,
            ];
        }

        $totalMessages = $thread->messages()->count();
        $offset = max(0, $totalMessages - ($page * self::MESSAGES_PER_PAGE));

        $messages = $thread->messages()
            ->with(['user' => function ($query) {
                $query->select('id', 'name');
            }])
            ->orderBy('created_at', 'asc')
            ->skip($offset)
            ->take(self::MESSAGES_PER_PAGE)
            ->get();

        $hasMore = $offset > 0;

        $mappedMessages = $messages->map(fn($msg) => [
            'id' => $msg->id,
            'thread_id' => $msg->thread_id,
            'user_id' => $msg->user_id,
            'body' => $msg->body,
            'created_at' => $msg->created_at->toIso8601String(),
            'updated_at' => $msg->updated_at->toIso8601String(),
            'user' => [
                'id' => $msg->user->id,
                'name' => $msg->user->name,
            ],
        ])->toArray();

        $userId = Auth::id();
        $participant = $thread->participants->firstWhere('user_id', $userId);
        $isUnread = false;

        if ($participant) {
            $isUnread = $participant->last_read === null || $thread->updated_at->gt($participant->last_read);
        }

        return [
            'data' => $mappedMessages,
            'hasMore' => $hasMore,
            'thread' => [
                'id' => $thread->id,
                'subject' => $thread->subject,
                'updated_at' => $thread->updated_at->toIso8601String(),
                'is_unread' => $isUnread,
                'other_participants' => $thread->participants->where('user_id', '!=', $userId)->map(function ($participant) {
                    return [
                        'id' => $participant->user->id,
                        'name' => $participant->user->name,
                    ];
                })->values(),
                'participants' => $thread->participants->map(function ($participant) {
                    return [
                        'id' => $participant->user->id,
                        'name' => $participant->user->name,
                    ];
                })->values(),
            ],
        ];
    }
}
