<?php

namespace App\Filament\Admin\Resources\PartnerBills\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;

use Filament\Actions\Action;
use Filament\Actions\EditAction;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;

use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

use App\Enum\PartnerBillStatus;
use App\Models\PartnerBill;

class PartnerBillsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('admin/partnerBill.fields.code'))
                    ->searchable(),
                TextColumn::make('address')
                    ->label(__('admin/partnerBill.fields.address'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')
                    ->label(__('admin/partnerBill.fields.phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('date')
                    ->label(__('admin/partnerBill.fields.date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label(__('admin/partnerBill.fields.start_time'))
                    ->time()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('end_time')
                    ->label(__('admin/partnerBill.fields.end_time'))
                    ->time()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('final_total')
                    ->label(__('admin/partnerBill.fields.final_total'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('event_name')
                    ->label(__('admin/partnerBill.fields.event'))
                    ->getStateUsing(fn(PartnerBill $record): string => $record->event?->name ?? $record->custom_event ?? 'N/A')
                    ->searchable(query: function ($query, string $search): void {
                        $query->whereHas('event', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })->orWhere('custom_event', 'like', "%{$search}%");
                    })
                    ->sortable(query: function ($query, string $direction): void {
                        $query->orderByRaw("COALESCE((SELECT name FROM events WHERE events.id = partner_bills.event_id), custom_event) {$direction}");
                    }),
                TextColumn::make('client.name')
                    ->label(__('admin/partnerBill.fields.client'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('partner.name')
                    ->label(__('admin/partnerBill.fields.partner'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state ?? 'N/A'),
                TextColumn::make('category.name')
                    ->label(__('admin/partnerBill.fields.category'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('admin/partnerBill.fields.status'))
                    ->searchable()
                    ->formatStateUsing(fn($state) => $state->label())
                    ->color(fn($state) => match ($state) {
                        PartnerBillStatus::PENDING => 'warning',
                        PartnerBillStatus::CANCELLED => 'danger',
                        PartnerBillStatus::COMPLETED => 'success',
                        PartnerBillStatus::CONFIRMED => 'info',
                        PartnerBillStatus::IN_JOB => 'info',
                        PartnerBillStatus::EXPIRED => 'secondary',
                        default => 'secondary',
                    })
                    ->badge(),
                TextColumn::make('created_at')
                    ->label(__('admin/partnerBill.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin/partnerBill.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('changeStatus')
                    ->label(__('admin/partnerBill.actions.change_status'))
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('warning')
                    ->schema([
                        Select::make('status')
                            ->label(__('admin/partnerBill.fields.status'))
                            ->options([
                                PartnerBillStatus::PENDING->value => PartnerBillStatus::PENDING->label(),
                                PartnerBillStatus::CONFIRMED->value => PartnerBillStatus::CONFIRMED->label(),
                                PartnerBillStatus::IN_JOB->value => PartnerBillStatus::IN_JOB->label(),
                                PartnerBillStatus::COMPLETED->value => PartnerBillStatus::COMPLETED->label(),
                                PartnerBillStatus::EXPIRED->value => PartnerBillStatus::EXPIRED->label(),
                                PartnerBillStatus::CANCELLED->value => PartnerBillStatus::CANCELLED->label(),
                            ])
                            ->default(fn(PartnerBill $record): string => $record->status->value)
                            ->required()
                            ->native(false),
                    ])
                    ->action(function (PartnerBill $record, array $data): void {
                        $oldStatus = $record->status->label();
                        $record->update([
                            'status' => PartnerBillStatus::from($data['status']),
                        ]);
                        $newStatus = $record->status->label();

                        Notification::make()
                            ->title(__('admin/partnerBill.notifications.status_changed'))
                            ->body(__('admin/partnerBill.notifications.status_changed_body', [
                                'code' => $record->code,
                                'old_status' => $oldStatus,
                                'new_status' => $newStatus,
                            ]))
                            ->success()
                            ->send();
                    })
                    ->modalHeading(__('admin/partnerBill.actions.change_status'))
                    ->modalDescription(__('admin/partnerBill.actions.change_status_description'))
                    ->modalSubmitActionLabel(__('admin/partnerBill.actions.update'))
                    ->modalWidth('md'),
                Action::make('viewArrivalPhoto')
                    ->label(__('admin/partnerBill.fields.arrival_photo'))
                    ->icon('heroicon-o-photo')
                    ->color('info')
                    ->url(fn(PartnerBill $record): ?string => $record->getFirstMedia('arrival_photo')?->getUrl())
                    ->openUrlInNewTab()
                    ->visible(fn(PartnerBill $record): bool => $record->getFirstMedia('arrival_photo') !== null),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
