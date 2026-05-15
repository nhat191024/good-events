<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVerified
{
    /**
     * Redirect to verification method selection if the user has not verified
     * their email or phone number.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user->hasVerifiedEmail() && ! $user->hasVerifiedPhone()) {
            return redirect()->route('verification.method');
        }

        return $next($request);
    }
}
