<?php

namespace App\Filament\Admin\Resources\Partners\Tables;

use App\Enum\Role;
use App\Filament\Admin\Resources\Partners\PartnerResource;
use App\Models\Partner;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
// use Filament\Actions\BulkActionGroup;
// use Filament\Actions\DeleteBulkAction;
// use Filament\Actions\RestoreBulkAction;
// use Filament\Actions\ForceDeleteBulkAction;

use STS\FilamentImpersonate\Actions\Impersonate;

class PartnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('avatar')
                    ->label(__('admin/partner.fields.label.avatar'))
                    ->formatStateUsing(function ($state, Partner $record) {
                        if ($record->avatar) {
                            $url = asset($record->avatar);

                            return '<img src="'.e($url).'" alt="Avatar" style="height:80px;max-width:80px;object-fit:contain;border-radius:100px;">';
                        }

                        return '';
                    })
                    ->html(),
                TextColumn::make('name')
                    ->label(__('admin/partner.fields.label.name'))
                    ->searchable(),
                TextColumn::make('partnerProfile.partner_name')
                    ->label(__('admin/partner.fields.label.partner_name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('admin/partner.fields.label.email'))
                    ->searchable(),
                TextColumn::make('country_code')
                    ->label(__('admin/partner.fields.label.country_code'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('admin/partner.fields.label.phone'))
                    ->searchable(),
                TextColumn::make('partnerProfile.identity_card_number')
                    ->label(__('admin/partner.fields.label.identity_card_number'))
                    ->searchable(),
                TextColumn::make('wallet.balance')
                    ->label(__('admin/partner.fields.label.wallet_balance'))
                    ->money('VND')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('email_verified_at')
                    ->label(__('admin/partner.fields.label.email_verified_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('admin/partner.fields.label.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin/partner.fields.label.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin/partner.fields.label.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->default('trashed'),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('walletTransactions')
                        ->label(__('admin/partner.actions.wallet_transactions'))
                        ->icon('heroicon-o-wallet')
                        ->url(fn (Partner $record): string => PartnerResource::getUrl('wallet-transactions', [
                            'record' => $record,
                        ])),
                    Action::make('deposit')
                        ->label(__('admin/partner.actions.deposit'))
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->schema([
                            TextInput::make('amount')
                                ->label(__('admin/partner.fields.label.deposit_amount'))
                                ->numeric()
                                ->required()
                                ->minValue(1000)
                                ->step(1000)
                                ->suffix('VND')
                                ->helperText(__('admin/partner.helpers.minimum_deposit')),
                        ])
                        ->action(function (Partner $record, array $data): void {
                            try {
                                $amount = (int) $data['amount'];
                                $meta = [
                                    'reason' => __('admin/partner.messages.admin_deposit'),
                                    'old_balance' => $record->balanceInt,
                                    'new_balance' => $record->balanceInt + $amount,
                                ];

                                $record->deposit($amount, $meta);

                                Notification::make()
                                    ->success()
                                    ->title(__('admin/partner.notifications.deposit_success.title'))
                                    ->body(__('admin/partner.notifications.deposit_success.body', [
                                        'amount' => number_format($amount, 0, ',', '.'),
                                        'partner' => $record->name,
                                    ]))
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->danger()
                                    ->title(__('admin/partner.notifications.deposit_error.title'))
                                    ->body($e->getMessage())
                                    ->send();
                            }
                        })
                        ->modalHeading(__('admin/partner.modals.deposit_heading'))
                        ->modalSubmitActionLabel(__('admin/partner.actions.confirm_deposit'))
                        ->modalWidth('md'),
                    Action::make('manage_services')
                        ->label('Quản lý dịch vụ')
                        ->icon('heroicon-o-rectangle-stack')
                        ->disabled(fn (Partner $record) => $record->deleted_at !== null)
                        ->tooltip(fn ($record): ?string => $record->deleted_at ? __('admin/partner.tooltips.manage_services_disabled') : null)
                        ->url(fn (Partner $record): string => PartnerResource::getUrl('services', ['record' => $record])),
                    Impersonate::make()
                        ->redirectTo(route('filament.partner.pages.dashboard')),
                    // EditAction::make(),
                    DeleteAction::make()
                        ->label(__('global.ban'))
                        ->modalHeading(__('admin/user.ban_title'))
                        ->modalDescription(__('admin/user.ban_description'))
                        ->schema([
                            Textarea::make('reason')
                                ->label(__('admin/partner.fields.label.ban_reason'))
                                ->placeholder(__('admin/partner.placeholders.ban_reason'))
                                ->required()
                                ->maxLength(1000)
                                ->rows(4),
                        ])
                        ->before(function (Partner $record, array $data): void {
                            $record->update([
                                'ban_reason' => $data['reason'],
                            ]);

                            activity('partner_ban')
                                ->causedBy(auth()->user())
                                ->performedOn($record)
                                ->event('banned')
                                ->withProperty('reason', $data['reason'])
                                ->log(__('admin/partner.activity.banned', [
                                    'partner' => $record->name,
                                ]));
                        })
                        ->modalSubmitActionLabel(__('global.ban'))
                        ->successNotificationTitle(__('admin/user.ban_success_message')),
                    RestoreAction::make()
                        ->after(fn (Partner $record) => $record->update([
                            'ban_reason' => null,
                        ])),
                    Action::make('view_ban_reason')
                        ->label(__('admin/partner.actions.view_ban_reason'))
                        ->icon('heroicon-o-document-magnifying-glass')
                        ->color('warning')
                        ->visible(fn (Partner $record): bool => ($record->trashed() || ! $record->can_accept_shows) && filled($record->ban_reason))
                        ->fillForm(fn (Partner $record): array => [
                            'ban_reason' => $record->ban_reason,
                        ])
                        ->schema([
                            Textarea::make('ban_reason')
                                ->label(__('admin/partner.fields.label.ban_reason'))
                                ->disabled()
                                ->rows(6),
                        ])
                        ->modalHeading(__('admin/partner.modals.ban_reason_heading'))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel(__('global.close')),
                    Action::make('ban_accept_show')
                        ->label(__('admin/partner.actions.ban_accept_show'))
                        ->icon('heroicon-o-minus-circle')
                        ->color('danger')
                        ->visible(fn (Partner $record): bool => $record->deleted_at === null && $record->can_accept_shows)
                        ->schema([
                            Textarea::make('reason')
                                ->label(__('admin/partner.fields.label.ban_reason'))
                                ->placeholder(__('admin/partner.placeholders.ban_accept_show_reason'))
                                ->required()
                                ->maxLength(1000)
                                ->rows(4),
                        ])
                        ->action(function (Partner $record, array $data): void {
                            $record->update([
                                'can_accept_shows' => false,
                                'ban_reason' => $data['reason'],
                            ]);

                            activity('partner_show_ban')
                                ->causedBy(auth()->user())
                                ->performedOn($record)
                                ->event('show_banned')
                                ->withProperty('reason', $data['reason'])
                                ->log(__('admin/partner.activity.show_banned', [
                                    'partner' => $record->name,
                                ]));

                            Notification::make()
                                ->success()
                                ->title(__('admin/partner.ban_success_message'))
                                ->send();
                        })
                        ->modalHeading(__('admin/partner.modals.ban_accept_show_heading'))
                        ->modalSubmitActionLabel(__('admin/partner.actions.ban_accept_show')),
                    Action::make('ban_accept_hide')
                        ->label(__('admin/partner.actions.ban_accept_hide'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Partner $record): bool => $record->deleted_at === null && ! $record->can_accept_shows)
                        ->action(function (Partner $record): void {
                            $record->update([
                                'can_accept_shows' => true,
                                'ban_reason' => null,
                            ]);

                            Notification::make()
                                ->success()
                                ->title(__('admin/partner.unban_success_message'))
                                ->send();
                        }),
                    Action::make('approve_partner')
                        ->label(__('admin/partner.actions.view_personal_information'))
                        ->icon('heroicon-o-credit-card')
                        ->color('info')

                        ->modalHeading(__('admin/partner.view_partner_personal_information'))
                        ->modalContent(fn (Partner $record) => view('filament.admin.modals.view-partner-id-card', [
                            'record' => $record,
                        ]))
                        ->modalWidth('3xl')
                        ->slideOver()
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        ->extraModalFooterActions(fn (Partner $record): array => [
                            Action::make('approve')
                                ->label(__('global.approve'))
                                ->color('success')
                                ->visible(fn () => $record->partnerProfile && ! $record->partnerProfile->is_legit)
                                ->requiresConfirmation()
                                ->action(function () use ($record) {
                                    $record->partnerProfile?->update(['is_legit' => true]);
                                    Notification::make()->success()->title('Đã phê duyệt')->send();
                                }),
                            Action::make('unapprove')
                                ->label('Hủy phê duyệt')
                                ->color('danger')
                                ->visible(fn () => $record->partnerProfile && $record->partnerProfile->is_legit)
                                ->requiresConfirmation()
                                ->action(function () use ($record) {
                                    $record->partnerProfile?->update(['is_legit' => false]);
                                    Notification::make()->success()->title('Đã hủy phê duyệt')->send();
                                }),
                            Action::make('close')
                                ->label(__('global.close'))
                                ->color('gray')
                                ->cancelParentActions(),
                        ]),
                ]),
            ])
            ->recordUrl(function (Partner $record) {
                if (auth()->user()->hasRole(Role::SUPER_ADMIN)) {
                    return PartnerResource::getUrl('edit', ['record' => $record]);
                }

                return null;
            })
            ->toolbarActions([
                //
            ]);
    }
}
