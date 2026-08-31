<?php

namespace App\Filament\Admin\Pages;

use App\Enum\NavigationGroup;
use App\Services\PaymentService;
use App\Settings\PayOSSettings;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Throwable;

class PayOSWebhookManager extends SettingsPage
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = PayOSSettings::class;

    private ?string $originalWebhookUrl = null;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::SETTINGS->value;
    }

    public static function getNavigationLabel(): string
    {
        return __('admin/setting.payos_webhook.navigation_label');
    }

    public function getTitle(): string
    {
        return __('admin/setting.payos_webhook.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin/setting.payos_webhook.section_title'))
                    ->description(__('admin/setting.payos_webhook.section_description'))
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('webhook_url')
                            ->label(__('admin/setting.payos_webhook.webhook_url'))
                            ->helperText(__('admin/setting.payos_webhook.webhook_url_helper'))
                            ->url()
                            ->required()
                            ->maxLength(2048),
                    ]),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['webhook_url'] ??= route('payos.webhook');

        return $data;
    }

    protected function beforeSave(): void
    {
        $this->originalWebhookUrl = app(PayOSSettings::class)->webhook_url;
    }

    protected function afterSave(): void
    {
        $webhookUrl = (string) data_get($this->data, 'webhook_url');

        if ($webhookUrl === $this->originalWebhookUrl) {
            return;
        }

        try {
            app(PaymentService::class)->confirmWebhook($webhookUrl);
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->danger()
                ->title(__('admin/setting.payos_webhook.confirmation_failed'))
                ->body($exception->getMessage())
                ->send();

            throw (new Halt)->rollBackDatabaseTransaction();
        }
    }
}
