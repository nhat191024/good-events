<?php

namespace App\Filament\Admin\Pages;

use UnitEnum;
use BackedEnum;

use App\Enum\NavigationGroup;

use App\Settings\AppSettings;

use Filament\Pages\SettingsPage;
use Filament\Support\Icons\Heroicon;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class AppManager extends SettingsPage
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;
    protected static string $settings = AppSettings::class;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::SETTINGS->value;
    }

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
                Section::make('Thông tin chung')
                    ->description('Các thông tin cơ bản về ứng dụng')
                    ->columns(2)
                    ->schema([
                        TextInput::make('app_name')
                            ->label(__(__('admin/setting.fields.name')))
                            ->columnSpanFull()
                            ->required(),

                        TextInput::make('app_title')
                            ->label(__(__('admin/setting.fields.title')))
                            ->columnSpanFull()
                            ->required(),

                        Textarea::make('app_description')
                            ->label(__(__('admin/setting.fields.description')))
                            ->columnSpanFull()
                            ->required(),

                        FileUpload::make('app_logo')
                            ->label(__(__('admin/setting.fields.logo')))
                            ->image()
                            ->directory('uploads/app')
                            ->disk('public')
                            ->visibility('public')
                            ->formatStateUsing(fn($state) => $state ? str_replace('storage/', '', $state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(function ($state, $record) {
                                if (filled($state)) {
                                    if (str_starts_with($state, 'images/')) {
                                        return $state;
                                    }
                                    return 'storage/' . $state;
                                }
                                return $record?->app_logo ?? null;
                            }),

                        FileUpload::make('app_favicon')
                            ->label(__(__('admin/setting.fields.favicon')))
                            ->image()
                            ->directory('uploads/app')
                            ->disk('public')
                            ->visibility('public')
                            ->formatStateUsing(fn($state) => $state ? str_replace('storage/', '', $state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(function ($state, $record) {
                                if (filled($state)) {
                                    if (str_starts_with($state, 'images/')) {
                                        return $state;
                                    }
                                    return 'storage/' . $state;
                                }
                                return $record?->app_favicon ?? null;
                            }),
                    ]),

                Section::make('Tiêu đề các trang')
                    ->description('Cấu hình tiêu đề cho các trang danh mục')
                    ->columns(2)
                    ->schema([
                        TextInput::make('app_partner_title')
                            ->label(__(__('admin/setting.fields.titles.partner')))
                            ->columnSpanFull()
                            ->required(),

                        TextInput::make('app_design_title')
                            ->label(__(__('admin/setting.fields.titles.design')))
                            ->columnSpanFull()
                            ->required(),

                        TextInput::make('app_rental_title')
                            ->label(__(__('admin/setting.fields.titles.rental')))
                            ->columnSpanFull()
                            ->required(),
                    ]),

                Section::make('Thông tin liên hệ')
                    ->description('Thông tin hiển thị ở footer hoặc trang liên hệ')
                    ->columns(2)
                    ->schema([
                        TextInput::make('contact_hotline')
                            ->label(__(__('admin/setting.fields.contact_hotline')))
                            ->required(),

                        TextInput::make('contact_email')
                            ->label(__(__('admin/setting.fields.contact_email')))
                            ->required(),
                    ]),

                Section::make('Mạng xã hội')
                    ->description('Liên kết đến các trang mạng xã hội')
                    ->columns(2)
                    ->schema([
                        TextInput::make('social_facebook')
                            ->label(__(__('admin/setting.fields.socials.facebook')))
                            ->required(),

                        TextInput::make('social_facebook_group')
                            ->label(__(__('admin/setting.fields.socials.facebook_group')))
                            ->required(),

                        TextInput::make('social_zalo')
                            ->label(__(__('admin/setting.fields.socials.zalo')))
                            ->columnSpanFull()
                            ->required(),

                        TextInput::make('social_youtube')
                            ->label(__(__('admin/setting.fields.socials.youtube')))
                            ->required(),

                        TextInput::make('social_tiktok')
                            ->label(__(__('admin/setting.fields.socials.tiktok')))
                            ->required(),
                    ]),
            ]);
    }
}
