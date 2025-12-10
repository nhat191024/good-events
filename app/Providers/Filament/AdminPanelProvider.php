<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Pages\Dashboard;

use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;

use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use Jacobtims\FilamentLogger\FilamentLoggerPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BinaryBuilds\FilamentFailedJobs\FilamentFailedJobsPlugin;
use Boquizo\FilamentLogViewer\FilamentLogViewerPlugin;

use App\Filament\Admin\Widgets\AdminStatisticsWidget;
use App\Filament\Admin\Widgets\AdminRevenueChart;
use App\Filament\Admin\Widgets\AdminTopPartnersWidget;

use App\Enum\FilamentNavigationGroup;

use App\Settings\AppSettings;

use App\Filament\Admin\Pages\ListLogs;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $settings = app(AppSettings::class);

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Red,
            ])
            ->brandName('Sự Kiện tốt - Admin')
            ->favicon($settings->app_favicon)
            ->maxContentWidth(Width::Full)
            ->navigationGroups(FilamentNavigationGroup::class)

            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')

            ->viteTheme('resources/css/filament/admin/theme.css')

            ->pages([
                Dashboard::class,
            ])

            ->widgets([
                AdminStatisticsWidget::class,
                AdminRevenueChart::class,
                AdminTopPartnersWidget::class,
            ])

            ->plugins([
                FilamentShieldPlugin::make()
                    ->navigationGroup('settings')
                    ->globallySearchable(false)
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
                FilamentLoggerPlugin::make(),
                FilamentFailedJobsPlugin::make(),
                FilamentLogViewerPlugin::make()
                    ->listLogs(ListLogs::class)
                    ->navigationGroup('system')
                    ->navigationLabel(__('global.log_viewer'))
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
