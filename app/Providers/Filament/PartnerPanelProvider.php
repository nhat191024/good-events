<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Pages\Dashboard;

use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;

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

use Illuminate\Support\Facades\Auth;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PartnerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $user = Auth::user();
        $balance = $user ? $user->balance : 0;
        $balanceLabel = __('global.balance') . ': ' . number_format($balance, 0) . ' ' . __('global.currency');

        return $panel
            ->id('partner')
            ->path('partner')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->maxContentWidth(Width::Full)

            ->userMenuItems([
                'profile' => fn(Action $action) => $action->url(route('filament.partner.pages.profile-settings')),
                Action::make('balance')
                    ->label($balanceLabel)
                    ->disabled(true)
                    ->icon('heroicon-o-currency-dollar'),
            ])

            ->discoverResources(in: app_path('Filament/Partner/Resources'), for: 'App\Filament\Partner\Resources')
            ->discoverPages(in: app_path('Filament/Partner/Pages'), for: 'App\Filament\Partner\Pages')
            ->discoverWidgets(in: app_path('Filament/Partner/Widgets'), for: 'App\Filament\Partner\Widgets')

            ->viteTheme('resources/css/filament/partner/theme.css')

            ->pages([
                Dashboard::class,
            ])

            ->widgets([
                PartnerStatisticsWidget::class,
                PartnerRevenueChart::class,
                PartnerTopServicesWidget::class,
                AccountWidget::class,
                FilamentInfoWidget::class,
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
            ]);
    }
}
