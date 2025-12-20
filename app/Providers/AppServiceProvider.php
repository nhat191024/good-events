<?php

namespace App\Providers;

use Inertia\Inertia;

use BezhanSalleh\LanguageSwitch\LanguageSwitch;

use App\Settings\AppSettings;
use App\Enum\FilamentNavigationGroup;

use App\Auth\PolymorphicUserProvider;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;

use Spatie\Tags\Tag;
use App\Policies\TagPolicy;

use BeyondCode\Vouchers\Models\Voucher;
use App\Policies\VoucherPolicy;

use Spatie\Activitylog\Models\Activity;
use App\Policies\ActivityPolicy;

use BinaryBuilds\FilamentFailedJobs\Models\FailedJob;
use App\Policies\FailedJobPolicy;

use Tapp\FilamentMailLog\Models\MailLog;
use App\Policies\MailLogPolicy;

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
    public function boot(): void
    {
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(100)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Auth::provider('polymorphic', function ($app, array $config) {
            return new PolymorphicUserProvider($app['hash'], $config['model']);
        });

        $policies = [
            Tag::class => TagPolicy::class,
            Voucher::class => VoucherPolicy::class,
            Activity::class => ActivityPolicy::class,
            FailedJob::class => FailedJobPolicy::class,
            MailLog::class => MailLogPolicy::class,
        ];

        //manually register policies - why? idk
        foreach ($policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

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

        try {
            $settings = app(AppSettings::class);
            $settingsArray = $this->getSettingsArray($settings);
        } catch (\Exception $e) {
            $settingsArray = [
                'app_name'    => config('app.name'),
                'app_logo'    => null,
                'app_favicon' => null,
                'contact_hotline' => null,
                'contact_email' => null,
            ];
        }

        Inertia::share([
            'flash' => function () {
                return [
                    'success' => session('success'),
                    'error'   => session('error'),
                ];
            },
            'app_settings'  => fn() => $settingsArray
        ]);

        View::share('settings', $settingsArray);
    }

    private function getSettingsArray(AppSettings $settings)
    {
        try {
            return [
                'app_name'    => $settings->app_name,
                'app_logo'    => secure_asset($settings->app_logo),
                'app_favicon' => secure_asset($settings->app_favicon),
                'contact_hotline' => $settings->contact_hotline,
                'contact_email' => $settings->contact_email,
            ];
        } catch (\Exception $e) {
            return [
                'app_name'    => config('app.name'),
                'app_logo'    => null,
                'app_favicon' => null,
                'contact_hotline' => null,
                'contact_email' => null,
            ];
        }
    }
}
