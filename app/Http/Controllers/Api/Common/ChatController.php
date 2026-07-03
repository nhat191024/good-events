<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatMessageRequest;

use App\Models\Thread;
use App\Models\Message;

use App\Jobs\SendMessage;
use App\Support\ChatMessagePayload;

use Cmgmyr\Messenger\Models\Participant;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $user = Auth::user();
        $userId = $user?->id;
        $userRole = $user->roles->pluck('name')->first();
        $sideRequest = $request->input('side', null);

        if ($userId === null) {
            return response()->json([
                'threads' => [],
                'has_more' => false,
                'current_page' => 1,
            ]);
        }

        $searchTerm = $request->input('search', '');
        $page = max(1, (int) $request->input('page', 1));

        $with = [
            'latestMessage.media',
            'latestMessage.user' => function ($query) {
                $query->select('id', 'name');
            },
            'participants',
            'participants.user' => function ($query) {
                $query->select('id', 'name');
            },
            'bill' => function ($query) {
                $query->select('id', 'code', 'thread_id', 'event_id', 'custom_event', 'client_id', 'partner_id', 'category_id', 'date', 'start_time', 'end_time', 'address');
            },
            'bill.event' => function ($query) {
                $query->select('id', 'name');
            },
            'bill.category' => function ($query) {
                $query->select('id', 'name');
            },
        ];

        // check side request to determine first, use role if side request is null
        if ($sideRequest === 'partner' || ($sideRequest === null && $userRole === 'partner')) {
            $with['bill.client'] = function ($query) {
                $query->select('id', 'name');
            };
        }

        if ($sideRequest === 'client' || ($sideRequest === null && $userRole === 'client')) {
            $with['bill.partner'] = function ($query) {
                $query->select('id', 'name');
            };
        }

        $query = Thread::forUserOrderByNotReadMessages($userId)
            ->with($with)
            ->orderBy('threads.updated_at', 'desc');

        if (!empty(trim($searchTerm))) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('subject', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('participants.user', function ($userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'like', '%' . $searchTerm . '%');
                    })
                    ->orWhereHas('bill', function ($billQuery) use ($searchTerm) {
                        $billQuery->where('code', 'like', '%' . $searchTerm . '%');
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

        $mappedThreads = $threads->map(function ($thread) use ($sideRequest, $userId, $userRole) {
            $isUnread = false;
            $participant = $thread->participants->firstWhere('user_id', $userId);

            if ($participant) {
                $isUnread = $participant->last_read !== null && $thread->updated_at->gt($participant->last_read);
            }

            $subjectUser = null;

            if ($sideRequest === 'partner' || ($sideRequest === null && $userRole === 'partner')) {
                $subjectUser = $thread->bill?->client?->name;
            } elseif ($sideRequest === 'client' || ($sideRequest === null && $userRole === 'client')) {
                $subjectUser = $thread->bill?->partner?->name;
            }

            $subject = "{$subjectUser} - " . ($thread->bill->category_id ? $thread->bill->category?->name : 'No Category');

            return [
                'id' => $thread->id,
                'subject' => $subject,
                'is_unread' => $isUnread,
                'code' => $thread->bill->code,
                'participants' => $thread->participants->map(function ($participant) {
                    return [
                        'name' => $participant->user->name,
                    ];
                })->values(),
                'latest_message' => $thread->latestMessage ? [
                    'body' => $thread->latestMessage->body,
                    'type' => $thread->latestMessage->type,
                    'attachments' => null,
                    'location' => null,
                    'preview_text' => $thread->latestMessage->preview_text,
                    'sender_name' => $thread->latestMessage->user->name,
                    'created_at' => $thread->latestMessage->created_at?->diffForHumans(),
                ] : null,
                'bill' => $thread->bill ? [
                    'id' => $thread->bill->id,
                    'event_name' => $thread->bill->event_id ? $thread->bill->event?->name : $thread->bill->custom_event,
                    'datetime' => $thread->bill->date && $thread->bill->start_time && $thread->bill->end_time
                        ? $thread->bill->date->format('d/m/Y') . ' - ' . $thread->bill->start_time->format('H:i') . ' - ' . $thread->bill->end_time->format('H:i')
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
            }, 'media'])
            ->orderBy('created_at', 'asc')
            ->skip($offset)
            ->take(self::MESSAGES_PER_PAGE)
            ->get();

        $hasMore = $offset > 0;

        $mappedMessages = $messages->map(fn($msg) => [
            'sender_id' => $msg->user_id,
            'message' => ChatMessagePayload::message($msg),
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
    public function sendMessage(StoreChatMessageRequest $request, int $threadId)
    {
        $userId = Auth::id();

        try {
            $participant = Participant::where([
                'thread_id' => $threadId,
                'user_id' => $userId,
            ])->first();

            if (!$participant) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant of this thread.',
                ], 403);
            }

            $participant->last_read = now();
            $participant->save();

            $message = Message::create($request->messageAttributes($threadId, $userId));

            foreach ($request->file('images', []) as $image) {
                $message
                    ->addMedia($image)
                    ->toMediaCollection(Message::MEDIA_COLLECTION_CHAT_IMAGES);
            }

            $message->load('user', 'media');

            $formattedMessage = ChatMessagePayload::forDispatch($message, Auth::user());

            SendMessage::dispatch($formattedMessage);

            return response()->json([
                'success' => true,
                'message' => ChatMessagePayload::response($message, Auth::user()),
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
