<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

use BezhanSalleh\LanguageSwitch\LanguageSwitch;

use App\Settings\AppSettings;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Inertia\Inertia;
use Vite;

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

        // if ($this->app->environment('production') || $this->app->environment('testing')) {
        //     URL::forceScheme('https');
        // }
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
