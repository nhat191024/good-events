<?php

namespace App\Http\Controllers\Api\Common;

use App\Enum\Role;
use App\Models\Customer;
use App\Models\Partner;
use App\Models\User;

use App\Services\PasswordResetMailService;
use App\Services\PhoneLoginService;
use App\Services\AppleTokenVerifier;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Api\UserResource;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
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

        if (!$user->hasVerifiedEmail() && !$user->hasVerifiedPhone()) {
            $token = $user->createToken('mobile')->plainTextToken;
            return response()->json([
                'code' => 'UNVERIFIED',
                'token' => $token,
                'role' => $this->resolvePrimaryRole($user),
                'user' => new UserResource($user),
            ], 403);
        }

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
        if (!$user || $user->is_delete_account) {
            return response()->json([
                'message' => 'Account not found.',
            ], 404);
        }

        if (!$user->hasVerifiedEmail() && !$user->hasVerifiedPhone()) {
            $token = $user->createToken('mobile')->plainTextToken;
            return response()->json([
                'code' => 'UNVERIFIED',
                'token' => $token,
                'role' => $this->resolvePrimaryRole($user),
                'user' => new UserResource($user),
            ], 403);
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
     * POST /api/auth/apple
     *
     * Body: identity_token, authorization_code, email?, given_name?, family_name?
     * Response: { token, token_type, role, user } (401/404 on failure)
     *
     * @param Request $request
     * @param AppleTokenVerifier $appleTokenVerifier
     * @return JsonResponse
     */
    public function loginApple(Request $request, AppleTokenVerifier $appleTokenVerifier): JsonResponse
    {
        $validated = $request->validate([
            'identity_token' => 'required|string',
            'authorization_code' => 'required|string',
            'email' => 'nullable|email',
            'given_name' => 'nullable|string',
            'family_name' => 'nullable|string',
        ]);

        try {
            $appleUser = $appleTokenVerifier->verify($validated['identity_token']);
        } catch (\Throwable $e) {
            Log::warning('Apple login token verification failed', [
                'error_class' => $e::class,
                'error_message' => $e->getMessage(),
                'configured_audiences' => array_values(array_filter([
                    config('services.apple.service_id'),
                    config('services.apple.ios_bundle_id'),
                ])),
                'token' => $this->decodeAppleTokenForLog($validated['identity_token']),
                'request_email' => $validated['email'] ?? null,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'message' => 'Invalid Apple token.',
            ], 401);
        }

        $user = $this->resolveUserByAppleId($appleUser['sub']);

        if (!$user) {
            $email = $appleUser['email'] ?? $validated['email'] ?? null;
            $user = $email ? $this->resolveUserByEmail($email) : null;
        }

        if (!$user) {
            return response()->json([
                'message' => 'Account not found.',
            ], 404);
        }

        if ($user->apple_id && $user->apple_id !== $appleUser['sub']) {
            return response()->json([
                'message' => 'Account is linked to a different Apple account.',
            ], 409);
        }

        if (!$user->hasVerifiedEmail() && !$user->hasVerifiedPhone()) {
            $token = $user->createToken('mobile')->plainTextToken;
            return response()->json([
                'code' => 'UNVERIFIED',
                'token' => $token,
                'role' => $this->resolvePrimaryRole($user),
                'user' => new UserResource($user),
            ], 403);
        }

        $user->update(['apple_id' => $appleUser['sub']]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'role' => $this->resolvePrimaryRole($user),
            'user' => new UserResource($user),
        ]);
    }

    /**
     * GET|POST /api/auth/apple/callback
     *
     * Return Apple web auth result to Android callback activity.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function appleCallback(Request $request)
    {
        $query = http_build_query($request->all());
        $callbackUrl = 'signinwithapple://callback' . ($query ? '?' . $query : '');

        return Redirect::away($callbackUrl);
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
        $user = $request->user();
        $user?->currentAccessToken()?->delete();
        $user->fcm_token = null;
        $user->save();

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
        $user = auth()->user();
        $valid = $user && ! $user->is_delete_account;
        $is_legit = $valid && $user->partnerProfile?->is_legit;

        return response()->json([
            'valid' => $valid,
            'is_legit' => $is_legit,
        ]);
    }

    /**
     * DELETE /api/account/delete
     *
     * Delete account endpoint
     *
     * Response: { success: bool }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $request->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $user->tokens()->delete();
        $user->update([
            'is_delete_account' => true,
            'fcm_token' => null,
        ]);
        $user->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Decode Apple JWT header and safe payload fields without trusting the token.
     * This is diagnostic-only; verification still happens in AppleTokenVerifier.
     *
     * @return array<string, mixed>
     */
    private function decodeAppleTokenForLog(string $identityToken): array
    {
        $segments = explode('.', $identityToken);

        if (count($segments) < 2) {
            return ['error' => 'Wrong number of JWT segments'];
        }

        $payload = $this->decodeJwtSegmentForLog($segments[1]);

        if (isset($payload['sub']) && is_string($payload['sub'])) {
            $payload['sub'] = $this->maskForLog($payload['sub']);
        }

        return [
            'header' => $this->decodeJwtSegmentForLog($segments[0]),
            'payload' => array_intersect_key($payload, array_flip([
                'iss',
                'aud',
                'exp',
                'iat',
                'sub',
                'email',
                'email_verified',
                'is_private_email',
            ])),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJwtSegmentForLog(string $segment): array
    {
        $base64 = strtr($segment, '-_', '+/');
        $base64 .= str_repeat('=', (4 - strlen($base64) % 4) % 4);
        $json = base64_decode($base64, true);

        if ($json === false) {
            return ['error' => 'Invalid base64url segment'];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : ['error' => 'Invalid JSON segment'];
    }

    private function maskForLog(string $value): string
    {
        if (strlen($value) <= 8) {
            return '***';
        }

        return substr($value, 0, 4) . '...' . substr($value, -4);
    }

    private function resolveUserByEmail(string $email): ?User
    {
        return $this->resolveUserByColumn('email', $email);
    }

    private function resolveUserByAppleId(string $appleId): ?User
    {
        return $this->resolveUserByColumn('apple_id', $appleId);
    }

    private function resolveUserByColumn(string $column, string $value): ?User
    {
        $user = User::where($column, $value)->first();

        if (!$user) {
            return null;
        }

        $modelType = DB::table('model_has_roles')
            ->where('model_id', $user->id)
            ->value('model_type');

        return match ($modelType) {
            Customer::class => Customer::withTrashed()->find($user->id),
            Partner::class => Partner::withTrashed()->find($user->id),
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
