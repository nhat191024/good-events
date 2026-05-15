<?php

namespace App\Services;

use App\Models\User;

class PhoneLoginService
{
    /**
     * Determine if the given input looks like a phone number rather than an email address.
     */
    public function isPhoneNumber(string $input): bool
    {
        // If it contains '@' it is treated as an email
        if (str_contains($input, '@')) {
            return false;
        }

        $stripped = preg_replace('/[\s\-.]/', '', $input);

        return (bool) preg_match('/^\+?\d+$/', $stripped);
    }

    /**
     * Normalize a phone number to the format 0XXXXXXXXX.
     * Strips country code (+84 / 84) and ensures a leading '0'.
     *
     * examples:
     *   0912345678   -> 0912345678 (unchanged)
     *   84912345678  -> 0912345678
     *   +84912345678 -> 0912345678
     *   912345678    -> 0912345678
     */
    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[\s\-.]/', '', $phone);

        $phone = ltrim($phone, '+');

        if (str_starts_with($phone, '84')) {
            $phone = substr($phone, 2);
        }

        if (! str_starts_with($phone, '0')) {
            $phone = '0' . $phone;
        }

        return $phone;
    }

    /**
     * Find the email address for a user whose phone number matches the given input.
     *
     * Returns null if no matching user is found.
     */
    public function findEmailByPhone(string $phone): ?string
    {
        $normalized = $this->normalizePhone($phone);

        $user = User::where('phone', $normalized)->first();

        return $user?->email;
    }
}
