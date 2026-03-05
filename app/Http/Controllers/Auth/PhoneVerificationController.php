<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PhoneVerificationController extends Controller
{
    /**
     * Mark the authenticated user's phone as verified.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedPhone()) {
            return redirect()->intended(default: route('home', absolute: false).'?verified=1');
        }

        $request->validate([
            'otp' => ['required', 'string'],
        ]);

        $otpService = app(OtpService::class);
        $phone = $request->user()->getPhoneForVerification();

        if ($otpService->verifyOtp($phone, $request->otp)) {
            $request->user()->markPhoneAsVerified();

            return redirect()->intended(route('home', absolute: false).'?verified=1');
        }

        return back()->withErrors(['otp' => 'The provided OTP is incorrect.']);
    }
}
