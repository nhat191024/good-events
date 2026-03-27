<?php

namespace App\Http\Controllers\Api\Common;

use App\Enum\Role;
use App\Models\Customer;
use App\Models\Partner;
use App\Models\User;

use App\Services\PasswordResetMailService;
use App\Services\PhoneLoginService;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Api\UserResource;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    /**
     * POST /api/login
     *
     * Body: email, password
     * Response: { token, token_type, role, user }
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $user = $request->user();
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'role' => $this->resolvePrimaryRole($user),
            'user' => new UserResource($user),
        ]);
    }

    /**
     * POST /api/login/google
     *
     * Body: access_token (Google access token)
     * Response: { token, token_type, role, user } (401/404 on failure)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function loginGoogle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'access_token' => 'required|string',
        ]);

        try {
            /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
            $driver = Socialite::driver('google');
            $googleUser = $driver->userFromToken($validated['access_token']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Invalid Google token.',
            ], 401);
        }

        $user = $this->resolveUserByEmail($googleUser->getEmail());
        if (!$user) {
            return response()->json([
                'message' => 'Account not found.',
            ], 404);
        }

        $user->update(['google_id' => $googleUser->getId()]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'role' => $this->resolvePrimaryRole($user),
            'user' => new UserResource($user),
        ]);
    }

    /**
     * POST /api/forgot
     *
     * Body: email
     * Response: { success: bool }
     *
     * @param Request $request
     * @param PasswordResetMailService $passwordResetMailService
     * @return JsonResponse
     */
    public function forgot(Request $request, PasswordResetMailService $passwordResetMailService, PhoneLoginService $phoneLoginService): JsonResponse
    {
        $validated = $request->validate([
            // Accept both email addresses and phone numbers
            'email' => ['required', 'string'],
        ]);

        $input = $validated['email'];

        // Resolve phone number to the registered email before sending the reset link
        if ($phoneLoginService->isPhoneNumber($input)) {
            $input = $phoneLoginService->findEmailByPhone($input) ?? $input;
        }

        $sent = $passwordResetMailService->sendResetLinkByEmail($input);

        return response()->json([
            'success' => $sent,
        ]);
    }

    /**
     * GET /api/logout
     *
     * Response: { success: true }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * GET /api/check-token
     *
     * Response: { valid: bool }
     *
     * @return JsonResponse
     */
    public function checkToken(): JsonResponse
    {
        return response()->json([
            'valid' => true,
        ]);
    }

    private function resolveUserByEmail(string $email): ?User
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return null;
        }

        $modelType = DB::table('model_has_roles')
            ->where('model_id', $user->id)
            ->value('model_type');

        return match ($modelType) {
            Customer::class => Customer::find($user->id),
            Partner::class => Partner::find($user->id),
            default => $user,
        };
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
