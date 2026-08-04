<?php

namespace App\Http\Controllers\Api\Common;

use App\Events\CallUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\AccessThreadCallRequest;
use App\Http\Requests\StartCallRequest;
use App\Http\Resources\Api\CallResource;
use App\Jobs\SendApnsVoipNotification;
use App\Jobs\SendMessage as SendMessageJob;
use App\Models\Call;
use App\Models\Message;
use App\Models\PushDevice;
use App\Models\Thread;
use App\Models\User;
use App\Services\AgoraTokenService;
use App\Services\ApnsVoipService;
use App\Services\FCMService;
use App\Support\ChatMessagePayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class CallController extends Controller
{
    public function __construct(
        private readonly AgoraTokenService $agoraTokenService,
        private readonly ApnsVoipService $apnsVoipService,
        private readonly FCMService $fcmService,
    ) {}

    public function store(StartCallRequest $request, int $thread): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $callTtl = max(60, (int) config('services.agora.call_ttl', 14400));

        $call = DB::transaction(function () use ($thread, $user, $validated, $callTtl): ?Call {
            Thread::query()->lockForUpdate()->findOrFail($thread);

            if (Call::query()->active()->where('thread_id', $thread)->exists()) {
                return null;
            }

            $uuid = (string) Str::ulid();
            $call = Call::query()->create([
                'uuid' => $uuid,
                'callkit_uuid' => (string) Str::uuid(),
                'thread_id' => $thread,
                'initiated_by' => $user->id,
                'channel' => "call_{$uuid}",
                'type' => $validated['type'],
                'status' => Call::STATUS_RINGING,
                'started_at' => now(),
                'expires_at' => now()->addSeconds($callTtl),
            ]);

            $call->invites()->createMany(
                collect($validated['invited_user_ids'])
                    ->map(fn (int $userId): array => ['user_id' => $userId])
                    ->all()
            );

            $call->participants()->create([
                'user_id' => $user->id,
                'joined_at' => now(),
            ]);

            return $call;
        });

        if ($call === null) {
            return response()->json([
                'message' => 'Cuộc trò chuyện này đang có một cuộc gọi khác.',
            ], 409);
        }

        $call = $this->loadCall($call);
        $payload = $this->callPayload($call, $request);

        CallUpdated::dispatch($payload);
        $this->notifyInvitedUsers($call, $user);

        return response()->json([
            'call' => $payload,
            'credentials' => $this->agoraTokenService->generateRtcToken(
                $call->channel,
                (int) $user->id,
            ),
        ], 201);
    }

    public function active(AccessThreadCallRequest $request, int $thread): JsonResponse
    {
        $call = Call::query()
            ->active()
            ->where('thread_id', $thread)
            ->latest('id')
            ->first();

        return response()->json([
            'call' => $call === null
                ? null
                : $this->callPayload($this->loadCall($call), $request),
        ]);
    }

    public function join(Request $request, Call $call): JsonResponse
    {
        Gate::authorize('join', $call);

        DB::transaction(function () use ($call, $request): void {
            $lockedCall = Call::query()->lockForUpdate()->findOrFail($call->id);
            Gate::authorize('join', $lockedCall);

            $lockedCall->participants()->updateOrCreate(
                ['user_id' => $request->user()->id],
                ['joined_at' => now(), 'left_at' => null],
            );

            $lockedCall->invites()
                ->where('user_id', $request->user()->id)
                ->where('status', 'pending')
                ->update(['status' => 'accepted', 'responded_at' => now()]);

            if ($lockedCall->status === Call::STATUS_RINGING) {
                $lockedCall->update(['status' => Call::STATUS_ACTIVE]);
            }
        });

        $call = $this->loadCall($call->refresh());
        $payload = $this->callPayload($call, $request);
        CallUpdated::dispatch($payload);

        return response()->json([
            'call' => $payload,
            'credentials' => $this->agoraTokenService->generateRtcToken(
                $call->channel,
                (int) $request->user()->id,
            ),
        ]);
    }

    public function leave(Request $request, Call $call): JsonResponse
    {
        Gate::authorize('view', $call);

        $call->participants()
            ->where('user_id', $request->user()->id)
            ->whereNull('left_at')
            ->update(['left_at' => now()]);

        $call = $this->loadCall($call->refresh());
        CallUpdated::dispatch($this->callPayload($call, $request));

        return response()->json(['success' => true]);
    }

    public function decline(Request $request, Call $call): JsonResponse
    {
        Gate::authorize('view', $call);

        $updated = $call->invites()
            ->where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->update(['status' => 'declined', 'responded_at' => now()]);

        if ($updated === 0) {
            return response()->json([
                'message' => 'Bạn không có lời mời cuộc gọi đang chờ.',
            ], 409);
        }

        $call = $this->loadCall($call->refresh());
        CallUpdated::dispatch($this->callPayload($call, $request));

        return response()->json(['success' => true]);
    }

    public function end(Request $request, Call $call): JsonResponse
    {
        Gate::authorize('end', $call);

        $message = DB::transaction(function () use ($call): Message {
            $endedAt = now();
            $connectedAt = $call->participants()
                ->where('user_id', '!=', $call->initiated_by)
                ->oldest('joined_at')
                ->first()
                ?->joined_at;

            $call->update([
                'status' => Call::STATUS_ENDED,
                'ended_at' => $endedAt,
            ]);

            $call->participants()
                ->whereNull('left_at')
                ->update(['left_at' => $endedAt]);

            return Message::query()->firstOrCreate(
                ['call_id' => $call->id],
                [
                    'thread_id' => $call->thread_id,
                    'user_id' => $call->initiated_by,
                    'type' => Message::TYPE_CALL,
                    'body' => null,
                    'call_duration_seconds' => $connectedAt === null
                        ? 0
                        : (int) $connectedAt->diffInSeconds($endedAt),
                ],
            );
        });

        $call = $this->loadCall($call->refresh());
        CallUpdated::dispatch($this->callPayload($call, $request));

        $message->load(['user', 'call']);
        SendMessageJob::dispatch(
            ChatMessagePayload::forDispatch($message, $call->initiator)
        );

        return response()->json(['success' => true]);
    }

    private function loadCall(Call $call): Call
    {
        return $call->load([
            'initiator:id,name,avatar',
            'invites.user:id,name,avatar',
            'participants.user:id,name,avatar',
        ]);
    }

    /** @return array<string, mixed> */
    private function callPayload(Call $call, Request $request): array
    {
        return (new CallResource($call))->toArray($request);
    }

    private function notifyInvitedUsers(Call $call, User $initiator): void
    {
        $invitedUsers = User::query()
            ->whereIn('id', $call->invites->pluck('user_id'))
            ->with('pushDevices')
            ->get();

        foreach ($invitedUsers as $invitedUser) {
            $notificationData = [
                'type' => 'incoming_call',
                'call_id' => $call->uuid,
                'callkit_uuid' => $call->callkit_uuid,
                'thread_id' => (string) $call->thread_id,
                'call_type' => $call->type,
                'initiator_id' => (string) $initiator->id,
                'initiator_name' => $initiator->name,
                'expires_at' => $call->expires_at->toIso8601String(),
            ];
            $sent = false;

            foreach ($invitedUser->pushDevices as $pushDevice) {
                $canSendVoip = $pushDevice->platform === PushDevice::PLATFORM_IOS
                    && $pushDevice->voip_token !== null
                    && $this->apnsVoipService->isConfigured();

                if ($canSendVoip) {
                    SendApnsVoipNotification::dispatch(
                        $pushDevice->id,
                        $call->uuid,
                        [
                            'aps' => ['content-available' => 1],
                            ...$notificationData,
                            'caller_name' => $initiator->name,
                            'handle' => (string) $initiator->id,
                            'has_video' => $call->type === Call::TYPE_VIDEO,
                        ],
                    );
                    $sent = true;

                    continue;
                }

                if ($pushDevice->fcm_token !== null) {
                    if ($pushDevice->platform === PushDevice::PLATFORM_ANDROID) {
                        $this->fcmService->sendIncomingCallToAndroid(
                            $pushDevice->fcm_token,
                            $call->uuid,
                            $notificationData,
                        );
                    } else {
                        $this->fcmService->sendToToken(
                            $pushDevice->fcm_token,
                            'Cuộc gọi đến',
                            "{$initiator->name} đang mời bạn tham gia cuộc gọi.",
                            $notificationData,
                            '10',
                        );
                    }
                    $sent = true;
                }
            }

            if (! $sent) {
                $sent = $this->fcmService->sendToUser(
                    $invitedUser,
                    'Cuộc gọi đến',
                    "{$initiator->name} đang mời bạn tham gia cuộc gọi.",
                    $notificationData,
                    '10',
                );
            }

            if ($sent) {
                $call->invites()
                    ->where('user_id', $invitedUser->id)
                    ->update(['notified_at' => now()]);
            }
        }
    }
}
