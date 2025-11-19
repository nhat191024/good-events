<?php

namespace App\Filament\Admin\Pages;

use UnitEnum;
use BackedEnum;

use App\Enum\NavigationGroup;

use App\Settings\PartnerSettings;

use Filament\Pages\SettingsPage;
use Filament\Support\Icons\Heroicon;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

use Filament\Forms\Components\TextInput;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class PartnerManager extends SettingsPage
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::SETTINGS->value;

    protected static string $settings = PartnerSettings::class;

    public static function getNavigationLabel(): string
    {
        return __('admin/setting.partner');
    }

    public function getTitle(): string
    {
        return __('admin/setting.partner');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin/setting.partner_settings'))
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('minimum_balance')
                            ->label(__(__('admin/setting.fields.minimum_balance')))
                            ->required(),

                        TextInput::make('fee_percentage')
                            ->label(__(__('admin/setting.fields.fee_percentage')))
                            ->required(),
                    ]),
            ]);
    }
}
