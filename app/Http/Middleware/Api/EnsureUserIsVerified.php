<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVerified
{
    /**
     * Reject API requests from users who have not verified their email or phone.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user->hasVerifiedEmail() || ! $user->hasVerifiedPhone()) {
            return response()->json([
                'code' => 'UNVERIFIED',
                'message' => __('Your account is not verified. Please verify your email or phone number.'),
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
