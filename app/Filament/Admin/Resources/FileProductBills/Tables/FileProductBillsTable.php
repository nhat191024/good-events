<?php

namespace App\Filament\Admin\Resources\FileProductBills\Tables;

use App\Enum\FileProductBillStatus;
use App\Enum\Role;
use App\Models\FileProductBill;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class FileProductBillsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('admin/fileProductBill.fields.id'))
                    ->searchable(),
                TextColumn::make('fileProduct.name')
                    ->label(__('admin/fileProductBill.fields.file_product_id'))
                    ->searchable(),
                TextColumn::make('client.name')
                    ->label(__('admin/fileProductBill.fields.client_id'))
                    ->searchable(),
                TextColumn::make('final_total')
                    ->label(__('admin/fileProductBill.fields.final_total'))
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('status')
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => match ($state) {
                        FileProductBillStatus::PENDING => 'info',
                        FileProductBillStatus::CANCELLED => 'danger',
                        FileProductBillStatus::PAID => 'success',
                        default => 'secondary',
                    })
                    ->badge(),
                TextColumn::make('created_at')
                    ->label(__('admin/fileProductBill.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin/fileProductBill.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                DateRangeFilter::make('created_at')
                    ->label(__('admin/fileProductBill.fields.created_at')),
                SelectFilter::make('status')
                    ->label(__('admin/fileProductBill.fields.status'))
                    ->options(FileProductBillStatus::asSelectArray()),
            ])
            ->recordActions([
                Action::make('changePaymentStatus')
                    ->label(__('admin/fileProductBill.actions.change_payment_status'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (): bool => auth()->user()?->hasAnyRole([
                        Role::SUPER_ADMIN,
                        Role::ADMIN,
                    ]) ?? false)
                    ->modalHeading(__('admin/fileProductBill.actions.change_payment_status'))
                    ->modalDescription(__('admin/fileProductBill.actions.change_payment_status_warning'))
                    ->modalSubmitActionLabel(__('admin/fileProductBill.actions.confirm_change'))
                    ->requiresConfirmation()
                    ->schema([
                        Select::make('status')
                            ->label(__('admin/fileProductBill.fields.status'))
                            ->options(fn (FileProductBill $record): array => self::availableStatuses($record))
                            ->required(),
                        Textarea::make('reason')
                            ->label(__('admin/fileProductBill.actions.reason'))
                            ->helperText(__('admin/fileProductBill.actions.reason_helper'))
                            ->required()
                            ->rows(3)
                            ->maxLength(1000),
                    ])
                    ->action(function (FileProductBill $record, array $data): void {
                        $previousStatus = $record->status;
                        $newStatus = FileProductBillStatus::from($data['status']);

                        DB::transaction(function () use ($record, $previousStatus, $newStatus, $data): void {
                            $record->forceFill([
                                'status' => $newStatus,
                                'final_total' => $newStatus === FileProductBillStatus::PAID
                                    ? ($record->final_total ?? $record->total)
                                    : $record->final_total,
                            ])->save();

                            activity('file_product_bill_manual_status')
                                ->causedBy(auth()->user())
                                ->performedOn($record)
                                ->withProperties([
                                    'previous_status' => $previousStatus->value,
                                    'new_status' => $newStatus->value,
                                    'reason' => $data['reason'],
                                ])
                                ->log('Admin manually changed file product bill payment status');
                        });

                        Notification::make()
                            ->success()
                            ->title(__('admin/fileProductBill.actions.status_updated'))
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    public static function availableStatuses(FileProductBill $record): array
    {
        return collect(FileProductBillStatus::cases())
            ->reject(fn (FileProductBillStatus $status): bool => $status === $record->status)
            ->mapWithKeys(fn (FileProductBillStatus $status): array => [$status->value => $status->label()])
            ->all();
    }
}
