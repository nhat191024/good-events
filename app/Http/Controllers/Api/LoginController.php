<?php

namespace App\Http\Controllers\Api;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Services\PasswordResetMailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $user = $request->user();
        $user->loadMissing('partnerProfile');
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'role' => $this->resolvePrimaryRole($user),
            'user' => new UserResource($user),
        ]);
    }

    public function loginGoogle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'access_token' => 'required|string',
        ]);

        try {
            $googleUser = Socialite::driver('google')->userFromToken($validated['access_token']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Invalid Google token.',
            ], 401);
        }

        $user = User::where('email', $googleUser->getEmail())->first();
        if (!$user) {
            return response()->json([
                'message' => 'Account not found.',
            ], 404);
        }

        if ($googleUser->getId()) {
            $user->forceFill(['google_id' => $googleUser->getId()])->save();
        }

        $user->loadMissing('partnerProfile');
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'role' => $this->resolvePrimaryRole($user),
            'user' => new UserResource($user),
        ]);
    }

    public function forgot(Request $request, PasswordResetMailService $passwordResetMailService): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $sent = $passwordResetMailService->sendResetLinkByEmail($validated['email']);

        return response()->json([
            'success' => $sent,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
        ]);
    }

    private function resolvePrimaryRole(User $user): ?string
    {
        if ($user->hasRole(Role::PARTNER)) {
            return Role::PARTNER->value;
        }

        if ($user->hasRole(Role::CLIENT)) {
            return Role::CLIENT->value;
        }

        return $user->roles()->pluck('name')->first();
    }
}
