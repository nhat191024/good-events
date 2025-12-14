<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Pages\Dashboard;

use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Enums\ThemeMode;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;

use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;

use App\Filament\Partner\Widgets\PartnerStatisticsWidget;
use App\Filament\Partner\Widgets\PartnerRevenueChart;
use App\Filament\Partner\Widgets\PartnerTopServicesWidget;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;

use Filament\Actions\Action;

use App\Models\Partner;
use App\Http\Middleware\CheckPartnerAccess;
use App\Livewire\Component\ChatNotificationIndicator;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use App\Settings\AppSettings;

class PartnerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        try {
            $settings = app(AppSettings::class);
            $favicon = asset($settings->app_favicon);
        } catch (\Exception $e) {
            $favicon = asset('images/favicon.ico');
        }

        return $panel
            ->id('partner')
            ->path('partner')
            ->authGuard('web')
            ->authPasswordBroker('users')
            ->emailVerification()
            ->passwordReset()

            ->brandName('Sự Kiện tốt - Đối tác')
            ->favicon($favicon)
            ->colors([
                'primary' => Color::Rose
            ])
            ->maxContentWidth(Width::Full)

            ->userMenuItems([
                Action::make('balance')
                    ->label(function () {
                        $user = Auth::user();
                        $balance = $user ? $user->balanceInt : 0;
                        return __('global.balance') . ': ' . number_format($balance, 0) . ' ' . __('global.currency');
                    })
                    ->disabled(true)
                    ->icon('heroicon-o-currency-dollar'),
                'profile' => fn(Action $action) => $action->url(route('filament.partner.pages.profile-settings')),
            ])

            ->databaseNotifications()
            ->lazyLoadedDatabaseNotifications(true)
            ->databaseNotificationsPolling('90s')

            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn() => Blade::render('@livewire(\'component.chat-notification-indicator\')')
            )

            ->discoverResources(in: app_path('Filament/Partner/Resources'), for: 'App\Filament\Partner\Resources')
            ->discoverPages(in: app_path('Filament/Partner/Pages'), for: 'App\Filament\Partner\Pages')
            ->discoverWidgets(in: app_path('Filament/Partner/Widgets'), for: 'App\Filament\Partner\Widgets')

            ->viteTheme('resources/css/filament/partner/theme.css')

            ->defaultThemeMode(ThemeMode::Light)

            ->pages([
                \App\Filament\Partner\Pages\Dashboard::class,
            ])

            ->widgets([
                PartnerStatisticsWidget::class,
                PartnerRevenueChart::class,
                PartnerTopServicesWidget::class,
            ])

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                CheckPartnerAccess::class,
            ]);
    }
}
