<?php

namespace App\Http\Controllers\Api\Common;

use App\Enum\CacheKey;

use App\Http\Controllers\Controller;

use App\Models\Thread;

use App\Jobs\SendMessage;

use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Models\Participant;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;

class ChatController extends Controller
{
    private const int THREADS_PER_PAGE = 14;
    private const int MESSAGES_PER_PAGE = 12;

    /**
     * GET /api/chat
     *
     * Query: search, page
     * Response: { threads, has_more, current_page }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $userId = Auth::id();

        if ($userId === null) {
            return response()->json([
                'threads' => [],
                'has_more' => false,
                'current_page' => 1,
            ]);
        }

        $searchTerm = $request->input('search', '');
        $page = max(1, (int) $request->input('page', 1));

        $query = Thread::forUserOrderByNotReadMessages($userId)
            ->with([
                'latestMessage.user' => function ($query) {
                    $query->select('id', 'name');
                },
                'participants',
                'participants.user' => function ($query) {
                    $query->select('id', 'name');
                },
                'bill' => function ($query) {
                    $query->select('id', 'thread_id', 'event_id', 'custom_event', 'date', 'start_time', 'address');
                },
                'bill.event' => function ($query) {
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
                $isUnread = $participant->last_read !== null && $thread->updated_at->gt($participant->last_read);
            }

            return [
                'id' => $thread->id,
                'subject' => $thread->subject,
                'is_unread' => $isUnread,
                'participants' => $thread->participants->map(function ($participant) {
                    return [
                        'name' => $participant->user->name,
                    ];
                })->values(),
                'latest_message' => $thread->latestMessage ? [
                    'body' => $thread->latestMessage->body,
                    'sender_name' => $thread->latestMessage->user->name,
                    'created_at' => $thread->latestMessage->created_at?->diffForHumans(),
                ] : null,
                'bill' => $thread->bill ? [
                    'id' => $thread->bill->id,
                    'event_name' => $thread->bill->event_id ? $thread->bill->event?->name : $thread->bill->custom_event,
                    'datetime' => $thread->bill->date && $thread->bill->start_time
                        ? $thread->bill->date->format('d/m/Y') . ' ' . $thread->bill->start_time->format('H:i')
                        : null,
                    'address' => $thread->bill->address,
                ] : null,
            ];
        });

        return response()->json([
            'threads' => $mappedThreads->values()->all(),
            'has_more' => $hasMore,
            'current_page' => $page,
        ]);
    }

    /**
     * GET /api/chat/threads/{thread}/messages
     *
     * Query: page
     * Response: { data, hasMore, thread }
     *
     * @param Request $request
     * @param int $threadId
     * @return \Illuminate\Http\JsonResponse
     */
    public function loadMessages(Request $request, int $threadId)
    {
        $page = (int) $request->input('page', 1);
        $thread = Thread::find($threadId);

        $thread?->markAsRead(Auth::id());

        if (!$thread) {
            return response()->json([
                'messages' => [],
                'hasMore' => false,
            ]);
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
            'sender_id' => $msg->user_id,
            'message' => [
                'id' => $msg->id,
                'thread_id' => $msg->thread_id,
                'user_id' => $msg->user_id,
                'body' => $msg->body,
                'created_at' => $msg->created_at?->toIso8601String(),
                'updated_at' => $msg->updated_at?->toIso8601String(),
            ],
            'user' => [
                'id' => $msg->user_id,
                'name' => $msg->user->name ?? 'Ghost',
            ],
        ])->toArray();

        return response()->json([
            'messages' => $mappedMessages,
            'hasMore' => $hasMore,
        ]);
    }

    /**
     * POST /api/chat/threads/{thread}/messages
     *
     * Body: body
     * Response: { success: true, message } or { success: false, message }
     *
     * @param Request $request
     * @param int $threadId
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(Request $request, int $threadId)
    {
        $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $userId = Auth::id();
        $messageBody = $request->input('body');

        try {
            $participant = Participant::where([
                'thread_id' => $threadId,
                'user_id' => $userId,
            ]);

            if (!$participant) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant of this thread.',
                ], 403);
            }

            $participant->last_read = now();

            $message = Message::create([
                'thread_id' => $threadId,
                'user_id' => $userId,
                'body' => $messageBody,
            ]);

            $formattedMessage = [
                'id' => $message->id,
                'thread_id' => $message->thread_id,
                'user_id' => $message->user_id,
                'body' => $message->body,
                'created_at' => $message->created_at,
                'updated_at' => $message->updated_at,
                'user' => [
                    'id' => $userId,
                    'name' => Auth::user() ? Auth::user()->name : 'Ghost',
                ],
                'other_participant_ids' => array_values(array_diff(
                    Cache::remember(
                        CacheKey::THREAD_PARTICIPANT->value . "{$threadId}",
                        now()->addWeek(),
                        fn() => Participant::where('thread_id', $threadId)->pluck('user_id')->all()
                    ),
                    [$userId]
                )),
            ];

            SendMessage::dispatch($formattedMessage);

            return response()->json([
                'success' => true,
            ]);
        } catch (QueryException $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send message.',
            ], 500);
        }
    }
}
