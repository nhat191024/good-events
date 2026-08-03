<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\InviteChatUserRequest;
use App\Http\Requests\SearchChatUserRequest;
use App\Models\ChatInvitation;
use App\Models\User;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatInvitationController extends Controller
{
    public function searchUsers(SearchChatUserRequest $request): JsonResponse
    {
        $phone = trim((string) $request->validated('phone'));

        $users = User::query()
            ->whereKeyNot($request->user()->id)
            ->where('phone', 'like', '%'.$phone.'%')
            ->select(['id', 'name', 'phone', 'avatar'])
            ->limit(20)
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'avatar' => $user->avatar_url,
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
}
