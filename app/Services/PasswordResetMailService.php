<?php

namespace App\Services;

use App\Models\User;
use App\Mail\PasswordResetLink;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

class PasswordResetMailService
{
    /**
     * Determine locale for a user
     */
    private function getUserLocale($user): string
    {
        return $user && property_exists($user, 'language') ? $user->language : config('app.locale', 'vi');
    }

    /**
     * Send password reset link via email
     */
    public function sendPasswordResetLink(User $user): void
    {
        try {
            if (!$user->email) {
                Log::warning('User has no email address', ['user_id' => $user->id]);
                return;
            }

            // Generate reset token
            $resetToken = Password::createToken($user);
            $userLocale = $this->getUserLocale($user);

            // Send reset link email
            Mail::to($user->email)
                ->send(new PasswordResetLink($user, $resetToken, $userLocale));

            Log::info('Password reset link sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send password reset link', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send password reset link by email address
     */
    public function sendResetLinkByEmail(string $email): bool
    {
        try {
            $user = User::where('email', $email)->first();

            if (!$user) {
                Log::info('Password reset requested for non-existent email', ['email' => $email]);
                return false;
            }

            $this->sendPasswordResetLink($user);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to process password reset request', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Handle password reset confirmation
     */
    public function resetPassword(User $user, string $password): void
    {
        try {
            $user->forceFill([
                'password' => bcrypt($password),
                'remember_token' => null,
            ])->save();

            event(new PasswordReset($user));

            Log::info('Password reset successfully', ['user_id' => $user->id]);
        } catch (\Exception $e) {
            Log::error('Failed to reset password', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}