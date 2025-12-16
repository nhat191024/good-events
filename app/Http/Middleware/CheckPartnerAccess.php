<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Enum\Role;

class CheckPartnerAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Check if user has partner role
        if (!$user->hasRole(Role::PARTNER->value)) {
            Auth::logout();
            return redirect()->route('filament.partner.auth.login')
                ->with('error', 'You do not have permission to access the partner panel.');
        }

        return $next($request);
    }
}
