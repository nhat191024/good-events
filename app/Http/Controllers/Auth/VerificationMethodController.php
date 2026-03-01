<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Inertia\Response;

class VerificationMethodController extends Controller
{
    /**
     * Show the view to select a verification method.
     */
    public function create(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail() && $user->hasVerifiedPhone()) {
            return redirect()->intended(route('home', absolute: false));
        }

        return Inertia::render('auth/SelectVerificationMethod', [
            'hasVerifiedEmail' => $user->hasVerifiedEmail(),
            'hasVerifiedPhone' => $user->hasVerifiedPhone(),
            // send user email obscured slightly
            'email' => $this->obscureEmail($user->email),
            // send user phone obscured slightly
            'phone' => $this->obscurePhone($user->phone),
        ]);
    }

    /**
     * Handle the selection of a verification method.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'method' => ['required', 'string', 'in:email,phone'],
        ]);

        $route = $request->input('method') === 'email' ? 'verification.notice' : 'verification.phone.notice';

        return redirect()->route($route);
    }

    private function obscureEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];

        $obscuredName = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 2));

        return $obscuredName . '@' . $domain;
    }

    private function obscurePhone(string $phone): string
    {
        if (strlen($phone) < 4) {
            return $phone;
        }

        return substr($phone, 0, 3) . str_repeat('*', strlen($phone) - 6) . substr($phone, -3);
    }
}
