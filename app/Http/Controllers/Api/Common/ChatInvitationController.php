<?php

namespace App\Http\Controllers\Api\Common;

use App\Enum\ChatMembershipContext;
use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\InviteChatUserRequest;
use App\Http\Requests\SearchChatUserRequest;
use App\Models\ChatInvitation;
use App\Models\Customer;
use App\Models\Partner;
use App\Models\User;
use App\Notifications\ChatInvitationNotification;
use App\Services\FCMService;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;

class ChatInvitationController extends Controller
{
    public function __construct(
        private readonly FCMService $fcmService,
    ) {}

    public function searchUsers(SearchChatUserRequest $request): JsonResponse
    {
        $phone = trim((string) $request->validated('phone'));

        $users = User::query()
            ->whereKeyNot($request->user()->id)
            ->where('phone', 'like', '%'.$phone.'%')
            ->select(['id', 'name', 'phone'])
            ->limit(20)
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
            ]);

        return response()->json(['users' => $users]);
    }

    public function invite(InviteChatUserRequest $request, int $thread): JsonResponse
    {
        $userId = (int) $request->validated('user_id');

        if (Participant::query()
            ->where('thread_id', $thread)
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->exists()) {
            return response()->json([
                'message' => 'Người dùng đã là thành viên của đoạn chat.',
            ], 422);
        }

        $invitation = DB::transaction(function () use ($request, $thread, $userId): ChatInvitation {
            $invitation = ChatInvitation::query()
                ->lockForUpdate()
                ->firstOrNew(['thread_id' => $thread, 'user_id' => $userId]);

            if ($invitation->exists && $invitation->status === ChatInvitation::STATUS_PENDING) {
                return $invitation;
            }

            $invitation->fill([
                'invited_by_user_id' => $request->user()->id,
                'status' => ChatInvitation::STATUS_PENDING,
                'accepted_at' => null,
                'left_at' => null,
            ])->save();

            return $invitation;
        });

        $this->notifyInvitedUser($invitation, $request->user());

        return response()->json([
            'message' => 'Đã gửi lời mời tham gia đoạn chat.',
            'invitation' => $this->invitationPayload($invitation),
        ], $invitation->wasRecentlyCreated ? 201 : 200);
    }

    public function accept(Request $request, int $thread): JsonResponse
    {
        $invitation = DB::transaction(function () use ($request, $thread): ?ChatInvitation {
            $invitation = ChatInvitation::query()
                ->where('thread_id', $thread)
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->first();

            if (! $invitation || $invitation->status !== ChatInvitation::STATUS_PENDING) {
                return null;
            }

            $participant = Participant::withTrashed()->firstOrNew([
                'thread_id' => $thread,
                'user_id' => $request->user()->id,
            ]);
            $participant->last_read = now();
            $participant->deleted_at = null;
            $participant->membership_context = ChatMembershipContext::Invitation->value;
            $participant->save();

            $invitation->update([
                'status' => ChatInvitation::STATUS_ACCEPTED,
                'accepted_at' => now(),
                'left_at' => null,
            ]);

            return $invitation;
        });

        if (! $invitation) {
            return response()->json(['message' => 'Không tìm thấy lời mời đang chờ.'], 404);
        }

        DatabaseNotification::query()
            ->where('notifiable_id', $request->user()->id)
            ->whereIn('notifiable_type', [User::class, Partner::class, Customer::class])
            ->whereNull('read_at')
            ->where('data->type', 'chat_invitation')
            ->where('data->thread_id', $thread)
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => 'Bạn đã tham gia đoạn chat.',
            'invitation' => $this->invitationPayload($invitation),
        ]);
    }

    public function leave(Request $request, int $thread): JsonResponse
    {
        $left = DB::transaction(function () use ($request, $thread): bool {
            $invitation = ChatInvitation::query()
                ->where('thread_id', $thread)
                ->where('user_id', $request->user()->id)
                ->where('status', ChatInvitation::STATUS_ACCEPTED)
                ->lockForUpdate()
                ->first();

            if (! $invitation) {
                return false;
            }

            $participant = Participant::query()
                ->where('thread_id', $thread)
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->first();

            if (! $participant) {
                return false;
            }

            $participant->delete();
            $invitation->update([
                'status' => ChatInvitation::STATUS_LEFT,
                'left_at' => now(),
            ]);

            return true;
        });

        if (! $left) {
            return response()->json([
                'message' => 'Chỉ thành viên được mời mới có thể rời đoạn chat.',
            ], 403);
        }

        return response()->json(['message' => 'Bạn đã rời đoạn chat.']);
    }

    /** @return array<string, mixed> */
    private function invitationPayload(ChatInvitation $invitation): array
    {
        return [
            'id' => $invitation->id,
            'thread_id' => $invitation->thread_id,
            'user_id' => $invitation->user_id,
            'invited_by_user_id' => $invitation->invited_by_user_id,
            'status' => $invitation->status,
            'accepted_at' => $invitation->accepted_at?->toIso8601String(),
            'left_at' => $invitation->left_at?->toIso8601String(),
        ];
    }

    private function notifyInvitedUser(ChatInvitation $invitation, User $inviter): void
    {
        $invitedUser = $this->resolveNotifiableUser($invitation->user_id);

        if (! $invitedUser) {
            return;
        }

        $title = __('notification.chat_invitation.title');
        $body = __('notification.chat_invitation.body', ['inviter' => $inviter->name]);
        $data = [
            'type' => 'chat_invitation',
            'code' => 'CHAT_INVITATION',
            'invitation_id' => (string) $invitation->id,
            'thread_id' => (string) $invitation->thread_id,
            'inviter_id' => (string) $inviter->id,
            'inviter_name' => $inviter->name,
        ];

        $invitedUser->notify(new ChatInvitationNotification(
            invitationId: $invitation->id,
            threadId: $invitation->thread_id,
            inviterId: $inviter->id,
            inviterName: $inviter->name,
        ));

        $sentToRegisteredDevice = false;

        foreach ($invitedUser->pushDevices as $pushDevice) {
            if ($pushDevice->fcm_token === null) {
                continue;
            }

            $this->fcmService->sendToToken(
                $pushDevice->fcm_token,
                $title,
                $body,
                $data,
                '10',
            );
            $sentToRegisteredDevice = true;
        }

        if (! $sentToRegisteredDevice && $invitedUser->fcm_token !== null) {
            $this->fcmService->sendToUser(
                $invitedUser,
                $title,
                $body,
                $data,
                '10',
            );
        }
    }

    private function resolveNotifiableUser(int $userId): ?User
    {
        $roles = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $userId)
            ->whereIn('model_has_roles.model_type', [
                User::class,
                Partner::class,
                Customer::class,
            ])
            ->pluck('roles.name');

        $modelClass = match (true) {
            $roles->contains(Role::PARTNER->value) => Partner::class,
            $roles->contains(Role::CLIENT->value) => Customer::class,
            default => User::class,
        };

        return $modelClass::query()
            ->with('pushDevices')
            ->find($userId);
    }
}
