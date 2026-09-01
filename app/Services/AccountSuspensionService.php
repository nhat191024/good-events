<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Laravel\Sanctum\PersonalAccessToken;

class AccountSuspensionService
{
    public function findSuspendedUserByToken(?string $token): ?User
    {
        if (! $token) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (! $accessToken) {
            return null;
        }

        if (! is_a($accessToken->tokenable_type, User::class, true)) {
            return null;
        }

        $user = User::withTrashed()->find($accessToken->tokenable_id);

        return $user && $this->isSuspended($user) ? $user : null;
    }

    public function isSuspended(User $user): bool
    {
        return $user->trashed() && filled($user->ban_reason);
    }

    public function response(User $user): JsonResponse
    {
        return response()->json([
            'code' => 'ACCOUNT_SUSPENDED',
            'message' => 'Account suspended.',
            'ban_reason' => $user->ban_reason,
            'suspended_at' => $user->deleted_at?->toIso8601String(),
        ], 403);
    }
}
