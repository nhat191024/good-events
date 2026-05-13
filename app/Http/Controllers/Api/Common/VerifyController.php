<?php

namespace App\Http\Controllers\Api\Common;

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

        if ($method === 'phone') {
            app(OtpService::class)->sendOtp($request->user()->phone);
        } else {
            app(EmailVerificationMailService::class)->sendVerificationLink($request->user());
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

        return response()->json(['message' => __('Invalid OTP')], 422);
    }
}
