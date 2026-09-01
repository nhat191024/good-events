<?php

namespace App\Http\Controllers\Api\Common;

use App\Enum\PartnerBillPriceIncreaseRequestStatus;
use App\Enum\PartnerBillStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatMessageRequest;
use App\Jobs\SendMessage;
use App\Models\ChatInvitation;
use App\Models\Customer;
use App\Models\Message;
use App\Models\Partner;
use App\Models\PartnerBill;
use App\Models\PartnerBillPriceIncreaseRequest;
use App\Models\Thread;
use App\Models\User;
use App\Support\ChatMessagePayload;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ChatController extends Controller
{
    private const int THREADS_PER_PAGE = 14;

    private const int MESSAGES_PER_PAGE = 12;

    /**
     * GET /api/chat
     *
     * Query: search, page
     * Response: { threads, has_more, current_page }
     */
    public function index(Request $request): JsonResponse
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
            'latestMessage.user' => function ($query) {
                $query->select('id', 'name', 'avatar');
            },
            'latestMessage.priceIncreaseRequest',
            'participants',
            'participants.user' => function ($query) {
                $query->select('id', 'name');
            },
            'chatInvitations' => function ($query) use ($userId) {
                $query
                    ->where('user_id', $userId)
                    ->where('status', ChatInvitation::STATUS_ACCEPTED);
            },
            'bill' => function ($query) {
                $query->select('id', 'code', 'thread_id', 'event_id', 'custom_event', 'client_id', 'partner_id', 'category_id', 'date', 'start_time', 'end_time', 'address', 'status', 'total', 'final_total');
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

        $requestingSide = in_array($sideRequest, ['client', 'partner'], true)
            ? $sideRequest
            : $userRole;

        if ($requestingSide === 'partner') {
            $query->whereHas('bill', function ($billQuery) use ($userId) {
                $billQuery->where('partner_id', $userId);
            });
        } elseif ($requestingSide === 'client') {
            $query->whereHas('bill', function ($billQuery) use ($userId) {
                $billQuery->where('client_id', $userId);
            });
        }

        if (! empty(trim($searchTerm))) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('subject', 'like', '%'.$searchTerm.'%')
                    ->orWhereHas('participants.user', function ($userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'like', '%'.$searchTerm.'%');
                    })
                    ->orWhereHas('bill', function ($billQuery) use ($searchTerm) {
                        $billQuery->where('code', 'like', '%'.$searchTerm.'%');
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

        $latestMessageSenderIds = $threads
            ->pluck('latestMessage.user_id')
            ->filter()
            ->unique()
            ->values();

        $avatarMediaByModel = Media::query()
            ->whereIn('model_id', $latestMessageSenderIds)
            ->whereIn('model_type', [User::class, Partner::class, Customer::class])
            ->where('collection_name', 'avatar')
            ->orderBy('order_column')
            ->get()
            ->keyBy(fn (Media $media): string => $media->model_type.':'.$media->model_id);

        $mappedThreads = $threads->map(function ($thread) use ($avatarMediaByModel, $sideRequest, $userId, $userRole) {
            $isUnread = false;
            $participant = $thread->participants->firstWhere('user_id', $userId);
            $canLeave = $thread->chatInvitations->isNotEmpty();
            $latestMessageSender = $thread->latestMessage?->user;
            $latestMessageSenderModelTypes = match ($thread->latestMessage?->user_id) {
                $thread->bill?->partner_id => [Partner::class, User::class, Customer::class],
                $thread->bill?->client_id => [Customer::class, User::class, Partner::class],
                default => [User::class, Partner::class, Customer::class],
            };
            $latestMessageSenderAvatarMedia = collect($latestMessageSenderModelTypes)
                ->map(fn (string $modelType): ?Media => $avatarMediaByModel->get($modelType.':'.$thread->latestMessage?->user_id))
                ->filter()
                ->first();
            $latestMessageSenderAvatarUrl = null;

            if ($latestMessageSender !== null) {
                $latestMessageSenderAvatarUrl = $latestMessageSenderAvatarMedia?->getAvailableUrl(['avatar_webp']);

                if (blank($latestMessageSenderAvatarUrl)) {
                    $latestMessageSenderAvatarUrl = $latestMessageSender->avatar_url;
                }
            }

            if ($participant) {
                $isUnread = $participant->last_read !== null && $thread->updated_at->gt($participant->last_read);
            }

            $subjectUser = null;

            if ($sideRequest === 'partner' || ($sideRequest === null && $userRole === 'partner')) {
                $subjectUser = $thread->bill?->client?->name;
            } elseif ($sideRequest === 'client' || ($sideRequest === null && $userRole === 'client')) {
                $subjectUser = $thread->bill?->partner?->name;
            }

            $subject = "{$subjectUser} - ".($thread->bill->category_id ? $thread->bill->category?->name : 'No Category');

            return [
                'id' => $thread->id,
                'subject' => $subject,
                'is_unread' => $isUnread,
                'can_leave' => $canLeave,
                'membership_source' => $canLeave ? 'invitation' : 'system',
                'code' => $thread->bill->code,
                'participants' => $thread->participants->map(function ($participant) {
                    return [
                        'id' => $participant->user?->id,
                        'name' => $participant->user?->name ?? 'Ghost',
                    ];
                })->values(),
                'latest_message' => $thread->latestMessage ? [
                    'body' => $thread->latestMessage->body,
                    'type' => $thread->latestMessage->type,
                    'attachments' => null,
                    'location' => null,
                    'preview_text' => $thread->latestMessage->preview_text,
                    'sender_name' => $latestMessageSender?->name ?? 'Ghost',
                    'sender_avatar' => $latestMessageSenderAvatarUrl,
                    'created_at' => $thread->latestMessage->created_at?->diffForHumans(),
                ] : null,
                'bill' => $thread->bill ? [
                    'id' => $thread->bill->id,
                    'event_name' => $thread->bill->event_id ? $thread->bill->event?->name : $thread->bill->custom_event,
                    'datetime' => $thread->bill->date && $thread->bill->start_time && $thread->bill->end_time
                        ? $thread->bill->date->format('d/m/Y').' - '.$thread->bill->start_time->format('H:i').' - '.$thread->bill->end_time->format('H:i')
                        : null,
                    'address' => $thread->bill->address,
                    'status' => $thread->bill->status,
                    'total' => $thread->bill->total,
                    'final_total' => $thread->bill->final_total,
                    'partner_id' => $thread->bill->partner_id,
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
     * @return JsonResponse
     */
    public function loadMessages(Request $request, int $threadId)
    {
        $page = (int) $request->input('page', 1);
        $userId = (int) Auth::id();
        $thread = Thread::query()
            ->with([
                'bill:id,thread_id,client_id,partner_id',
                'chatInvitations' => function ($query) use ($userId) {
                    $query
                        ->where('user_id', $userId)
                        ->where('status', ChatInvitation::STATUS_ACCEPTED);
                },
            ])
            ->find($threadId);

        $thread?->markAsRead(Auth::id());

        if (! $thread) {
            return response()->json([
                'messages' => [],
                'hasMore' => false,
            ]);
        }

        $totalMessages = $thread->messages()->count();
        $offset = max(0, $totalMessages - ($page * self::MESSAGES_PER_PAGE));

        $messages = $thread->messages()
            ->with(['user' => function ($query) {
                $query->select('id', 'name', 'avatar');
            }, 'media', 'call', 'priceIncreaseRequest'])
            ->orderBy('created_at', 'asc')
            ->skip($offset)
            ->take(self::MESSAGES_PER_PAGE)
            ->get();

        $hasMore = $offset > 0;

        $avatarMediaByModel = Media::query()
            ->whereIn('model_id', $messages->pluck('user_id')->unique())
            ->whereIn('model_type', [User::class, Partner::class, Customer::class])
            ->where('collection_name', 'avatar')
            ->orderBy('order_column')
            ->get()
            ->keyBy(fn (Media $media): string => $media->model_type.':'.$media->model_id);

        $mappedMessages = $messages->map(function (Message $message) use ($avatarMediaByModel, $thread): array {
            $senderModelTypes = match ($message->user_id) {
                $thread->bill?->partner_id => [Partner::class, User::class, Customer::class],
                $thread->bill?->client_id => [Customer::class, User::class, Partner::class],
                default => [User::class, Partner::class, Customer::class],
            };
            $senderAvatarMedia = collect($senderModelTypes)
                ->map(fn (string $modelType): ?Media => $avatarMediaByModel->get($modelType.':'.$message->user_id))
                ->filter()
                ->first();
            $senderAvatarUrl = $senderAvatarMedia?->getAvailableUrl(['avatar_webp']);

            if (blank($senderAvatarUrl)) {
                $senderAvatarUrl = $message->user?->avatar_url;
            }

            return [
                'sender_id' => $message->user_id,
                'message' => ChatMessagePayload::message($message),
                'user' => [
                    'id' => $message->user_id,
                    'name' => $message->user?->name ?? 'Ghost', // TODO: remove name after update app
                    'avatar' => $senderAvatarUrl,
                ],
            ];
        })->toArray();

        return response()->json([
            'messages' => $mappedMessages,
            'hasMore' => $hasMore,
            'thread' => [
                'id' => $thread->id,
                'can_leave' => $thread->chatInvitations->isNotEmpty(),
                'membership_source' => $thread->chatInvitations->isNotEmpty()
                    ? 'invitation'
                    : 'system',
            ],
        ]);
    }

    /**
     * POST /api/chat/threads/{thread}/messages
     *
     * Body: body
     * Response: { success: true, message } or { success: false, message }
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function sendMessage(StoreChatMessageRequest $request, int $threadId)
    {
        $userId = Auth::id();

        try {
            $participant = Participant::where([
                'thread_id' => $threadId,
                'user_id' => $userId,
            ])->first();

            if (! $participant) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant of this thread.',
                ], 403);
            }

            $participant->last_read = now();
            $participant->save();

            $messageAttributes = $request->messageAttributes($threadId, $userId);
            $clientMessageId = $messageAttributes['client_message_id'];

            if ($request->input('type') === Message::TYPE_PRICE_INCREASE_REQUEST) {
                $result = $this->createPriceIncreaseRequestMessage($request, $threadId, $userId);

                if ($result instanceof JsonResponse) {
                    return $result;
                }

                $message = $result;

                if (! $message->wasRecentlyCreated) {
                    return response()->json([
                        'success' => true,
                        'message' => ChatMessagePayload::response($message, Auth::user()),
                    ]);
                }

                $formattedMessage = ChatMessagePayload::forDispatch($message, Auth::user());

                SendMessage::dispatch($formattedMessage);

                return response()->json([
                    'success' => true,
                    'message' => ChatMessagePayload::response($message, Auth::user()),
                ]);
            }

            $message = $clientMessageId
                ? Message::firstOrCreate([
                    'thread_id' => $threadId,
                    'user_id' => $userId,
                    'client_message_id' => $clientMessageId,
                ], $messageAttributes)
                : Message::create($messageAttributes);

            if (! $message->wasRecentlyCreated) {
                $message->load('user', 'media', 'priceIncreaseRequest');

                return response()->json([
                    'success' => true,
                    'message' => ChatMessagePayload::response($message, Auth::user()),
                ]);
            }

            foreach ($request->file('images', []) as $image) {
                $message
                    ->addMedia($image)
                    ->toMediaCollection(Message::MEDIA_COLLECTION_CHAT_IMAGES);
            }

            $message->load('user', 'media', 'priceIncreaseRequest');

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

    private function createPriceIncreaseRequestMessage(
        StoreChatMessageRequest $request,
        int $threadId,
        int $userId,
    ): Message|JsonResponse {
        return DB::transaction(function () use ($request, $threadId, $userId): Message|JsonResponse {
            $clientMessageId = $request->input('client_message_id');

            $bill = PartnerBill::query()
                ->where('thread_id', $threadId)
                ->lockForUpdate()
                ->first();

            if (! $bill) {
                return response()->json(['message' => 'Thread is not associated with an order.'], 422);
            }

            if ($clientMessageId) {
                $existingMessage = Message::query()
                    ->where('thread_id', $threadId)
                    ->where('user_id', $userId)
                    ->where('client_message_id', $clientMessageId)
                    ->with(['user', 'media', 'priceIncreaseRequest'])
                    ->first();

                if ($existingMessage) {
                    return $existingMessage;
                }
            }

            if ((int) $bill->partner_id !== $userId) {
                return response()->json(['message' => 'Only the assigned partner can request a price increase.'], 403);
            }

            if (! in_array($bill->status, [PartnerBillStatus::CONFIRMED, PartnerBillStatus::IN_JOB], true)) {
                return response()->json(['message' => 'Order does not allow price increase requests.'], 422);
            }

            $requestedTotal = (int) $request->integer('requested_price');
            $originalTotal = (int) round((float) $bill->total);

            if ($requestedTotal <= $originalTotal) {
                return response()->json(['message' => 'Requested price must be greater than the current order total.'], 422);
            }

            $bill->priceIncreaseRequests()
                ->where('status', PartnerBillPriceIncreaseRequestStatus::Pending->value)
                ->update([
                    'status' => PartnerBillPriceIncreaseRequestStatus::Superseded->value,
                    'responded_at' => now(),
                ]);

            $message = Message::create([
                ...$request->messageAttributes($threadId, $userId),
                'body' => $request->string('reason')->trim()->toString(),
            ]);

            PartnerBillPriceIncreaseRequest::create([
                'partner_bill_id' => $bill->id,
                'partner_id' => $userId,
                'message_id' => $message->id,
                'original_total' => $originalTotal,
                'requested_total' => $requestedTotal,
                'reason' => $request->string('reason')->trim()->toString(),
                'status' => PartnerBillPriceIncreaseRequestStatus::Pending,
            ]);

            $message->load(['user', 'media', 'priceIncreaseRequest']);

            return $message;
        });
    }
}
