<?php

namespace App\Http\Controllers;

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use App\Enum\Role;
use Illuminate\Support\Facades\Auth;

class SocialiteController extends Controller
{
    public function redirect(string $provider)
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        $this->validateProvider($provider);

        $response = Socialite::driver($provider)->user();

        $user = User::firstWhere(['email' => $response->getEmail()]);

        if ($user) {
            $user->update([$provider . '_id' => $response->getId()]);
        } else {
            return redirect()->route('login')->with('status', 'Không tìm thấy tài khoản của bạn! Vui lòng đăng ký trước.');
        }

        Auth::login($user);

        $intendedUrl = session()->get('url.intended');

        if ($user->hasRole(Role::PARTNER)) {
            $intendedUrl = session()->pull('url.intended', route('filament.partner.pages.dashboard'));

            return redirect($intendedUrl);
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

        return redirect()->intended(route('home'));
    }

    protected function validateProvider(string $provider): void
    {
        if (! in_array($provider, ['google'])) {
            abort(404);
        }
    }
}
