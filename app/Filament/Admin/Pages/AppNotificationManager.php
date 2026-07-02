<?php

namespace App\Filament\Admin\Pages;

use BackedEnum;
use App\Enum\AppNotificationType;
use App\Enum\NavigationGroup;
use App\Settings\AppNotificationSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class AppNotificationManager extends SettingsPage
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static string $settings = AppNotificationSettings::class;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::SETTINGS->value;
    }

    public static function getNavigationLabel(): string
    {
        return __('admin/setting.notifications.navigation_label');
    }

    public function getTitle(): string
    {
        return __('admin/setting.notifications.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin/setting.notifications.sections.partner'))
                    ->columns(2)
                    ->schema($this->notificationFields('partner')),

                Section::make(__('admin/setting.notifications.sections.customer'))
                    ->columns(2)
                    ->schema($this->notificationFields('customer')),
            ]);
    }

    /**
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    private function notificationFields(string $prefix): array
    {
        return [
            Toggle::make("{$prefix}_enabled")
                ->label(__('admin/setting.notifications.fields.enabled'))
                ->live()
                ->columnSpanFull(),

            Select::make("{$prefix}_type")
                ->label(__('admin/setting.notifications.fields.type'))
                ->options(AppNotificationType::options())
                ->live()
                ->required(fn (Get $get): bool => (bool) $get("{$prefix}_enabled"))
                ->visible(fn (Get $get): bool => (bool) $get("{$prefix}_enabled"))
                ->columnSpanFull(),

            FileUpload::make("{$prefix}_notification_image")
                ->label(__('admin/setting.notifications.fields.notification_image'))
                ->image()
                ->directory("uploads/app-notifications/{$prefix}")
                ->disk('public')
                ->visibility('public')
                ->formatStateUsing(fn ($state) => $state ? str_replace('storage/', '', $state) : null)
                ->dehydrated(fn ($state): bool => filled($state))
                ->dehydrateStateUsing(fn ($state) => filled($state) ? 'storage/'.$state : null)
                ->required(fn (Get $get): bool => $this->isEnabledImageOnlyNotification($get, $prefix))
                ->visible(fn (Get $get): bool => $this->isEnabledImageOnlyNotification($get, $prefix))
                ->columnSpanFull(),

            TextInput::make("{$prefix}_title")
                ->label(__('admin/setting.notifications.fields.title'))
                ->required(fn (Get $get): bool => $this->isEnabledTextNotification($get, $prefix))
                ->visible(fn (Get $get): bool => $this->isEnabledTextNotification($get, $prefix))
                ->columnSpanFull(),

            RichEditor::make("{$prefix}_content")
                ->label(__('admin/setting.notifications.fields.content'))
                ->required(fn (Get $get): bool => $this->isEnabledTextNotification($get, $prefix))
                ->visible(fn (Get $get): bool => $this->isEnabledTextNotification($get, $prefix))
                ->columnSpanFull(),

            FileUpload::make("{$prefix}_image")
                ->label(__('admin/setting.notifications.fields.image'))
                ->image()
                ->directory("uploads/app-notifications/{$prefix}")
                ->disk('public')
                ->visibility('public')
                ->formatStateUsing(fn ($state) => $state ? str_replace('storage/', '', $state) : null)
                ->dehydrated(fn ($state): bool => filled($state))
                ->dehydrateStateUsing(fn ($state) => filled($state) ? 'storage/'.$state : null)
                ->required(fn (Get $get): bool => $this->isEnabledTextAndImageNotification($get, $prefix))
                ->visible(fn (Get $get): bool => $this->isEnabledTextAndImageNotification($get, $prefix))
                ->columnSpanFull(),
        ];
    }

    private function isTextNotification(?string $type): bool
    {
        return in_array($type, [
            AppNotificationType::TextOnly->value,
            AppNotificationType::TextAndImage->value,
        ], true);
    }

    private function isEnabledTextNotification(Get $get, string $prefix): bool
    {
        return (bool) $get("{$prefix}_enabled") && $this->isTextNotification($get("{$prefix}_type"));
    }

    private function isEnabledImageOnlyNotification(Get $get, string $prefix): bool
    {
        return (bool) $get("{$prefix}_enabled")
            && $get("{$prefix}_type") === AppNotificationType::ImageOnly->value;
    }

    private function isEnabledTextAndImageNotification(Get $get, string $prefix): bool
    {
        return (bool) $get("{$prefix}_enabled")
            && $get("{$prefix}_type") === AppNotificationType::TextAndImage->value;
    }
}
