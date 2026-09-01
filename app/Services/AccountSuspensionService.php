<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class AccountSuspensionService
{
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
