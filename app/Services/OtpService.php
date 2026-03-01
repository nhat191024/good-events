<?php

namespace App\Services;

use App\Settings\AppSettings;
use App\Enum\CacheKey;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class OtpService
{
    protected AppSettings $appSettings;
    protected ZaloService $zaloService;
    protected int $expiresIn = 5; // OTP expires time in minutes
    protected int $resendCooldown = 120; // Cooldown time for resending OTP in seconds
    protected int $maxAttempts = 3; // Maximum OTP requests allowed
    protected int $lockoutTime = 1440; // Timeout duration in minutes when max attempts are reached (24 hours)
    protected string $otpTemplateId;

    public function __construct(ZaloService $zaloService, AppSettings $appSettings)
    {
        $this->zaloService = $zaloService;
        $this->appSettings = $appSettings;
        $this->otpTemplateId = $appSettings->app_zalo_otp_template_id;
    }

    /**
     * Send OTP to a phone number via Zalo message
     *
     * @param string $phone phone number of the recipient (must be in international format, e.g. +84901234567)
     * @param string|null $mode Mode of operation (e.g. 'development' or null for production)
     * @return array Response from Zalo API
     */
    public function sendOtp(string $phone, ?string $mode = null): array
    {
        $attemptsKey = $this->getAttemptsCacheKey($phone);

        if (RateLimiter::tooManyAttempts($attemptsKey, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($attemptsKey);
            $hours = ceil($seconds / 3600);
            throw new \Exception(__('OTP Max Attempts', ['hours' => $hours]), 429);
        }

        if ($remainingSeconds = $this->getRecentOtpRemainingSeconds($phone)) {
            throw new \Exception(__('Resend OTP wait', ['seconds' => $remainingSeconds]), 429);
        }

        $otp = $this->generateOtp($phone);

        $templateData = [
            'otp' => $otp,
        ];

        $response = $this->zaloService->sendMessage(
            $mode === 'development' ? $this->appSettings->app_zalo_admin_phone : $phone,
            $mode,
            $this->otpTemplateId,
            $templateData
        );

        $this->markOtpRequestTime($phone);

        RateLimiter::hit($attemptsKey, $this->lockoutTime * 60);

        return $response;
    }

    /**
     * Verify if the provided OTP is valid for the given identifier
     *
     * @param string $identifier Identifier used to generate OTP (e.g. phone number, email, user_id)
     * @param string $otp OTP code provided by the user for verification
     * @return bool True if OTP is valid, false otherwise
     */
    public function verifyOtp(string $identifier, string $otp): bool
    {
        $cacheKey = $this->getCacheKey($identifier);
        $cachedOtp = Cache::get($cacheKey);

        if ($cachedOtp && $cachedOtp === $otp) {
            $this->clearOtp($identifier);
            RateLimiter::clear($this->getAttemptsCacheKey($identifier)); // Clear lockout when verified
            return true;
        }

        return false;
    }

    /**
     * create and store OTP
     *
     * @param string $identifier Identifier used to generate OTP
     * @param int $length Length of the OTP code (default is 6)
     * @return string Generated OTP code
     */
    public function generateOtp(string $identifier, int $length = 6): string
    {
        $otp = (string) random_int(10 ** ($length - 1), (10 ** $length) - 1);

        $cacheKey = $this->getCacheKey($identifier);

        Cache::put($cacheKey, $otp, now()->addMinutes($this->expiresIn));

        return $otp;
    }

    /**
     * Delete OTP if needed
     *
     * @param string $identifier identifier used to generate OTP
     */
    public function clearOtp(string $identifier): void
    {
        Cache::forget($this->getCacheKey($identifier));
    }

    /**
     * Generate a unique cache key for storing OTP based on the identifier
     *
     * @param string $identifier identifier used to generate OTP (e.g. phone number, email, user_id)
     * @return string cache key for storing OTP
     */
    protected function getCacheKey(string $identifier): string
    {
        return CacheKey::OTP_VERIFICATION->value . md5($identifier);
    }

    /**
     * Get the cache key for the last OTP request time
     */
    protected function getRateLimitCacheKey(string $identifier): string
    {
        return CacheKey::OTP_RESEND_COOLDOWN->value . md5($identifier);
    }

    /**
     * Get the cache key for tracking maximum OTP requests
     */
    protected function getAttemptsCacheKey(string $identifier): string
    {
        return CacheKey::OTP_ATTEMPTS->value . md5($identifier);
    }

    /**
     * Get remaining seconds before another OTP can be requested
     */
    protected function getRecentOtpRemainingSeconds(string $identifier): int
    {
        $timestamp = Cache::get($this->getRateLimitCacheKey($identifier));

        if ($timestamp) {
            $remaining = $timestamp - time();
            return $remaining > 0 ? (int)$remaining : 0;
        }

        return 0;
    }

    /**
     * Check if a recent OTP request was made within the cooldown period
     */
    protected function hasRecentOtpRequest(string $identifier): bool
    {
        return $this->getRecentOtpRemainingSeconds($identifier) > 0;
    }

    /**
     * Mark the time when an OTP was sent
     */
    protected function markOtpRequestTime(string $identifier): void
    {
        // Calculate expiration timestamp
        $expireAt = time() + $this->resendCooldown;

        Cache::put(
            $this->getRateLimitCacheKey($identifier),
            $expireAt,
            now()->addSeconds($this->resendCooldown)
        );
    }
}
