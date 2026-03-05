<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PhoneVerificationPromptController extends Controller
{
    /**
     * Show the phone verification prompt page.
     */
    public function __invoke(Request $request): RedirectResponse|Response
    {
        $user = $request->user();

        if ($user->hasVerifiedPhone()) {
            return redirect()->intended(route('home', absolute: false));
        }

        // Auto-send OTP on first visit (no status in session yet)
        if (! $request->session()->has('status')) {
            $user->sendPhoneVerificationNotification();
            $request->session()->put('status', 'otp-sent');
        }

        return Inertia::render('auth/VerifyPhone', ['status' => $request->session()->get('status')]);
    }
}
