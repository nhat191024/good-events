<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

use App\Enum\Role;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        $intendedUrl = session()->get('url.intended');

        if ($user->hasRole(Role::PARTNER)) {
            $intendedUrl = session()->pull('url.intended', route('filament.partner.pages.dashboard'));
            return Inertia::location($intendedUrl);
        }

        if ($intendedUrl) {
            $restrictedKeywords = ['partner', 'admin', 'filament'];
            $urlLower = strtolower($intendedUrl);
            
            $isRestricted = false;
            foreach ($restrictedKeywords as $keyword) {
                if (str_contains($urlLower, $keyword)) {
                    $isRestricted = true;
                    break;
                }
            }
            
            if ($isRestricted) {
                session()->forget('url.intended');
                return redirect()->route('home');
            }
        }

        return redirect()->intended(route('home', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
