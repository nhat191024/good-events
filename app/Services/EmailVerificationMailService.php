<?php

namespace App\Services;

use App\Mail\VerifyEmailLink;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class EmailVerificationMailService
{
    /**
     * verification url like VerifyEmail notification
     */
    private function buildVerificationUrl(User $user): string
    {
        $expireMinutes = (int) (Config::get('auth.verification.expire', 60));

        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes($expireMinutes),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );
    }

    /**
     *
     */
    private function getUserLocale(?User $user): string
    {
        return $user && property_exists($user, 'language') ? ($user->language ?: config('app.locale', 'vi')) : config('app.locale', 'vi');
    }

    /**
     * 
     */
    public function sendVerificationLink(User $user): void
    {
        try {
            if (! $user->email) {
                Log::warning('user has no email for verification', ['user_id' => $user->id]);

                return;
            }

            $verifyUrl = $this->buildVerificationUrl($user);
            $userLocale = $this->getUserLocale($user);

            Mail::to($user->email)->send(new VerifyEmailLink($user, $verifyUrl, $userLocale));

            Log::info('verification link sent', ['user_id' => $user->id, 'email' => $user->email]);
        } catch (\Throwable $e) {
            Log::error('failed to send verification link', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
