<?php

namespace App\Filament\Admin\Pages;

use UnitEnum;
use BackedEnum;

use App\Enum\FilamentNavigationGroup;

use App\Settings\AppSettings;

use Filament\Pages\SettingsPage;
use Filament\Support\Icons\Heroicon;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class AppManager extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;
    protected static string|UnitEnum|null $navigationGroup = FilamentNavigationGroup::SETTINGS;

    protected static string $settings = AppSettings::class;

    public static function getNavigationLabel(): string
    {
        return __('admin/setting.app');
    }

    public function getTitle(): string
    {
        return __('admin/setting.app');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin/setting.app_settings'))
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('app_name')
                            ->label(__(__('admin/setting.fields.name')))
                            ->columnSpanFull()
                            ->required(),

                        FileUpload::make('app_logo')
                            ->label(__(__('admin/setting.fields.logo')))
                            ->image()
                            ->directory('uploads/app')
                            ->disk('public')
                            ->visibility('public')
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(function ($state, $record) {
                                return filled($state) ? '/storage/' . $state : ($record?->app_logo ?? null);
                            }),

                        FileUpload::make('app_favicon')
                            ->label(__(__('admin/setting.fields.favicon')))
                            ->image()
                            ->directory('uploads/app')
                            ->disk('public')
                            ->visibility('public')
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(function ($state, $record) {
                                return filled($state) ? '/storage/' . $state : ($record?->app_favicon ?? null);
                            }),

                        FileUpload::make('app_partner_banner')
                            ->label(__(__('admin/setting.fields.partner_banner')))
                            ->image()
                            ->directory('uploads/app')
                            ->disk('public')
                            ->visibility('public')
                            ->columnSpanFull()
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(function ($state, $record) {
                                return filled($state) ? '/storage/' . $state : ($record?->app_partner_banner ?? null);
                            }),

                        FileUpload::make('app_design_banner')
                            ->label(__(__('admin/setting.fields.design_banner')))
                            ->image()
                            ->directory('uploads/app')
                            ->disk('public')
                            ->visibility('public')
                            ->columnSpanFull()
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(function ($state, $record) {
                                return filled($state) ? '/storage/' . $state : ($record?->app_design_banner ?? null);
                            }),

                        FileUpload::make('app_rental_banner')
                            ->label(__(__('admin/setting.fields.rental_banner')))
                            ->image()
                            ->directory('uploads/app')
                            ->disk('public')
                            ->visibility('public')
                            ->columnSpanFull()
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(function ($state, $record) {
                                return filled($state) ? '/storage/' . $state : ($record?->app_rental_banner ?? null);
                            }),
                    ]),
            ]);
    }
}
