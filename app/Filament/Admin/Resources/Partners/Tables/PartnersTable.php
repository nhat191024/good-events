<?php

namespace App\Filament\Admin\Resources\Partners\Tables;

use App\Models\User;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

// use Filament\Actions\BulkActionGroup;
// use Filament\Actions\DeleteBulkAction;
// use Filament\Actions\RestoreBulkAction;
// use Filament\Actions\ForceDeleteBulkAction;

class PartnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('avatar')
                    ->label(__('admin/partner.fields.label.avatar'))
                    ->formatStateUsing(function ($state, User $record) {
                        if ($record->avatar) {
                            $url = asset($record->avatar);
                            return '<img src="' . e($url) . '" alt="Avatar" style="height:80px;max-width:80px;object-fit:contain;border-radius:100px;">';
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
                    ->action(function (User $record, array $data): void {
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
                EditAction::make(),
                DeleteAction::make()
                    ->label(__('global.ban'))
                    ->modalHeading(__('admin/user.ban_title'))
                    ->modalDescription(__('admin/user.ban_description'))
                    ->modalSubmitActionLabel(__('global.ban'))
                    ->successNotificationTitle(__('admin/user.ban_success_message')),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
