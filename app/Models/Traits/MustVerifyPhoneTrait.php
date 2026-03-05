<?php

namespace App\Models\Traits;

use App\Services\OtpService;

trait MustVerifyPhoneTrait
{
    /**
     * Determine if the user has verified their phone number.
     */
    public function hasVerifiedPhone(): bool
    {
        return ! is_null($this->phone_verified_at);
    }

    /**
     * Mark the given user's phone as verified.
     */
    public function markPhoneAsVerified(): bool
    {
        return $this->forceFill([
            'phone_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Send the phone verification notification.
     *
     * @return void
     */
    public function sendPhoneVerificationNotification()
    {
        $mode = app()->environment() === 'production' ? null : 'development';
        app(OtpService::class)->sendOtp($this->getPhoneForVerification(), $mode);
    }

    /**
     * Get the phone number that should be used for verification.
     *
     * @return string
     */
    public function getPhoneForVerification()
    {
        $countryCode = $this->country_code ?? '+84';

        $phone = $this->phone;

        // if phone already starts with $+ or $countryCode, return it
        if (str_starts_with($phone, '+') || str_starts_with($phone, $countryCode)) {
            return $phone;
        }

        // if phone starts with 0, remove the 0
        if (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }

        return $countryCode . $phone;
    }
}
