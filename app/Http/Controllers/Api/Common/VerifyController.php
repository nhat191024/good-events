<?php

namespace App\Http\Controllers\Api\Common;

use App\Enum\CacheKey;
use App\Exceptions\OtpCooldownException;
use App\Exceptions\OtpMaxAttemptsException;
use App\Models\User;
use App\Services\OtpService;
use App\Services\EmailVerificationMailService;
use App\Services\PasswordResetMailService;
use App\Services\PhoneLoginService;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class VerifyController extends Controller
{
    /**
     * Send OTP to selected method (email or phone) for verification
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'method' => ['required', 'string', 'in:email,phone'],
        ]);

        $method = $request->input('method');

        try {
            if ($method === 'phone') {
                $request->user()->sendPhoneVerificationNotification();
            } else {
                app(EmailVerificationMailService::class)->sendVerificationLink($request->user());
            }
        } catch (OtpMaxAttemptsException $e) {
            $e = (int) $e->getMessage();
            return response()->json(['code' => 'MAX_ATTEMPTS', 'hours' => $e], 429);
        } catch (OtpCooldownException $e) {
            $e = (int) $e->getMessage();
            return response()->json(['code' => 'OTP_COOLDOWN', 'seconds' => $e], 429);
        }

        return response()->json(['message' => __('OTP Sent')], 200);
    }

    /**
     * Verify the provided OTP for phone verification
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'otp' => ['required', 'string'],
        ]);

        $otpService = app(OtpService::class);
        $phone = $request->user()->getPhoneForVerification();

        if ($otpService->verifyOtp($phone, $request->otp)) {
            $request->user()->markPhoneAsVerified();

            return response()->json(['message' => __('Phone Verified')], 200);
        }

        return response()->json(['code' => 'INVALID_OTP', 'message' => __('Invalid OTP')], 422);
    }

    /**
     * POST /api/forgot/send
     *
     * Send Zalo OTP to the phone number for password reset (unauthenticated).
     */
    public function sendForgotOtp(Request $request, PhoneLoginService $phoneLoginService): JsonResponse
    {
        $request->validate([
            'method' => ['required', 'string', 'in:phone,email'],
            'credential' => ['required', 'string'],
        ]);

        $method = $request->input('method');
        $credential = $request->input('credential');

        $user = null;

        if ($method === 'phone') {
            $user = User::where('phone', $phoneLoginService->normalizePhone($credential))->first();
        } else {
            $user = User::where('email', $credential)->first();
        }

        if (!$user) {
            return response()->json(['code' => 'USER_NOT_FOUND'], 404);
        }

        try {
            if ($method === 'phone') {
                $user->sendPhoneVerificationNotification();

                return response()->json(['message' => __('OTP Sent')], 200);
            } else {
                app(PasswordResetMailService::class)->sendResetLinkByEmail($credential);

                return response()->json(['message' => __('Password reset link sent if email exists')], 200);
            }
        } catch (OtpMaxAttemptsException $e) {
            $e = (int) $e->getMessage();
            return response()->json(['code' => 'MAX_ATTEMPTS', 'hours' => $e], 429);
        } catch (OtpCooldownException $e) {
            $e = (int) $e->getMessage();
            return response()->json(['code' => 'OTP_COOLDOWN', 'seconds' => $e], 429);
        }
    }

    /**
     * POST /api/forgot/verify-otp
     *
     * Verify Zalo OTP for password reset (unauthenticated).
     * Returns a short-lived reset_token to protect the reset-password endpoint.
     */
    public function verifyForgotOtp(Request $request, PhoneLoginService $phoneLoginService): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'otp' => ['required', 'string'],
        ]);

        $phone = $request->input('phone');
        $normalizedPhone = $phoneLoginService->normalizePhone($phone);

        $user = User::where('phone', $normalizedPhone)->first();

        if (!$user) {
            return response()->json(['code' => 'INVALID_OTP', 'message' => __('Invalid OTP')], 422);
        }

        $otpService = app(OtpService::class);

        if (!$otpService->verifyOtp($user->getPhoneForVerification(), $request->input('otp'))) {
            return response()->json(['code' => 'INVALID_OTP', 'message' => __('Invalid OTP')], 422);
        }

        $resetToken = Str::uuid()->toString();
        $cacheKey = CacheKey::PASSWORD_RESET_TOKEN->value . hash('sha256', $resetToken);

        Cache::put($cacheKey, $user->id, now()->addMinutes(15));

        return response()->json(['reset_token' => $resetToken], 200);
    }

    /**
     * POST /api/forgot/reset-password
     *
     * Reset password using the reset_token issued after OTP verification.
     * The token is one-time use and expires after 15 minutes.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'reset_token' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'password.min' => 'MIN_LENGTH_NOT_MET',
            'password.letters' => 'MISSING_LETTERS',
            'password.mixed' => 'MISSING_MIXED_CASE',
            'password.numbers' => 'MISSING_NUMBERS',
            'password.symbols' => 'MISSING_SYMBOLS',
            'password.uncompromised' => 'PASSWORD_COMPROMISED',
            'password.confirmed' => 'PASSWORD_CONFIRMATION_MISMATCH',
        ]);

        $cacheKey = CacheKey::PASSWORD_RESET_TOKEN->value . hash('sha256', $request->input('reset_token'));
        $userId = Cache::pull($cacheKey); // One-time use: pull removes the key

        if (!$userId) {
            return response()->json(['code' => 'INVALID_TOKEN', 'message' => __('Invalid or expired reset token.')], 422);
        }

        $user = User::find($userId);

        if (!$user || $user->is_delete_account) {
            return response()->json(['code' => 'INVALID_TOKEN', 'message' => __('Invalid or expired reset token.')], 422);
        }

        $user->update(['password' => Hash::make($request->input('password'))]);
        $user->tokens()->delete(); // Invalidate all existing sessions

        return response()->json(['message' => __('Password reset successfully.')], 200);
    }
}
