<?php

namespace App\Http\Controllers\Api\Common;

use App\Exceptions\OtpCooldownException;
use App\Exceptions\OtpMaxAttemptsException;
use App\Services\OtpService;
use App\Services\EmailVerificationMailService;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VerifyController extends Controller
{
    /**
     * Send OTP to selected method (email or phone) for verification
     */
    public function sendOtp(Request $request)
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
            return response()->json(['code' => 'MAX_ATTEMPTS', 'message' => $e->getMessage()], 429);
        } catch (OtpCooldownException $e) {
            return response()->json(['code' => 'OTP_COOLDOWN', 'message' => $e->getMessage()], 429);
        }

        return response()->json(['message' => __('OTP Sent')], 200);
    }

    /**
     * Verify the provided OTP for phone verification
     */
    public function verifyOtp(Request $request)
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
}
