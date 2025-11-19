<?php

namespace App\Providers;

use Inertia\Inertia;

use BezhanSalleh\LanguageSwitch\LanguageSwitch;

use App\Enum\Role;
use App\Settings\AppSettings;
use App\Enum\FilamentNavigationGroup;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(AppSettings $settings): void
    {
        //use if super admin can't access everything
        // Gate::before(function ($user, $ability) {
        //     return $user->hasRole(Role::SUPER_ADMIN->value) ? true : null;
        // });

        Filament::registerNavigationGroups([
            'system'   => NavigationGroup::make(fn() => FilamentNavigationGroup::SYSTEM->getLabel()),
            'settings' => NavigationGroup::make(fn() => FilamentNavigationGroup::SETTINGS->getLabel()),
        ]);

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['vi', 'en']); // also accepts a closure
        });

        Inertia::share([
            'flash' => function () {
                return [
                    'success' => session('success'),
                    'error'   => session('error'),
                ];
            },
            'app_settings'  => fn() => $this->getSettingsArray($settings)
        ]);

        View::share('settings', $this->getSettingsArray($settings));
    }

    private function getSettingsArray(AppSettings $settings)
    {
        try {
            return [
                'app_name'    => $settings->app_name,
                'app_logo'    => $settings->app_logo,
                'app_favicon' => $settings->app_favicon,
            ];
        } catch (\Exception $e) {
            return [
                'app_name'    => config('app.name'),
                'app_logo'    => null,
                'app_favicon' => null,
            ];
        }
    }
}
