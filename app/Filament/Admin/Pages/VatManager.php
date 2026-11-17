<?php

namespace App\Filament\Admin\Pages;

use App\Enum\NavigationGroup;
use App\Enum\Role;
use App\Settings\VatSettings;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class VatManager extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static string $settings = VatSettings::class;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::SETTINGS->value;
    }

    public static function getNavigationLabel(): string
    {
        return __('admin/setting.vat.navigation_label');
    }

    public function getTitle(): string
    {
        return __('admin/setting.vat.title');
    }

    /**
     * Only super admins may view or change the VAT rate.
     */
    public static function canAccess(): bool
    {
        return Filament::auth()?->user()?->hasRole(Role::SUPER_ADMIN->value) ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin/setting.vat.section_title'))
                    ->description(__('admin/setting.vat.section_description'))
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('percentage')
                            ->label(__('admin/setting.vat.percentage'))
                            ->helperText(__('admin/setting.vat.percentage_helper'))
                            ->integer()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required(),
                    ]),
            ]);
    }
}
