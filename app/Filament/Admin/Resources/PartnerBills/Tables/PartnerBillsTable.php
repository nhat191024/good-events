<?php

namespace App\Filament\Admin\Resources\PartnerBills\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;

use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

use App\Enum\PartnerBillStatus;
use App\Models\PartnerBill;

use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

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
                DateRangeFilter::make('created_at')
                    ->label(__('admin/partnerBill.fields.created_at')),
                SelectFilter::make('status')
                    ->label(__('admin/partnerBill.fields.status'))
                    ->options(PartnerBillStatus::asSelectArray()),
            ])
            ->recordActions([
                Action::make('cancelBill')
                    ->label(__('admin/partnerBill.actions.cancel_bill'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(PartnerBill $record): bool => $record->status != PartnerBillStatus::IN_JOB && $record->status != PartnerBillStatus::COMPLETED && $record->status != PartnerBillStatus::CANCELLED)
                    ->requiresConfirmation()
                    ->modalHeading(__('admin/partnerBill.actions.cancel_bill_heading'))
                    ->modalDescription(__('admin/partnerBill.actions.cancel_bill_description'))
                    ->action(function (PartnerBill $record): void {
                        $record->update([
                            'status' => PartnerBillStatus::CANCELLED,
                        ]);

                        Notification::make()
                            ->title(__('admin/partnerBill.notifications.bill_cancelled'))
                            ->body(__('admin/partnerBill.notifications.bill_cancelled_body', [
                                'code' => $record->code,
                            ]))
                            ->success()
                            ->send();
                    }),
                ActionGroup::make([
                    Action::make('viewArrivalPhoto')
                        ->label(__('admin/partnerBill.fields.arrival_photo'))
                        ->icon('heroicon-o-photo')
                        ->color('info')
                        ->modalHeading(__('admin/partnerBill.fields.arrival_photo'))
                        ->modalContent(fn(PartnerBill $record) => view('filament.admin.modals.arrival-photo', [
                            'media' => $record->getFirstMedia('arrival_photo'),
                        ]))
                        ->modalWidth('3xl')
                        ->slideOver()
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->visible(fn(PartnerBill $record): bool => $record->getFirstMedia('arrival_photo') !== null),
                    Action::make('viewCompletionPhoto')
                        ->label(__('admin/partnerBill.fields.completion_photo'))
                        ->icon('heroicon-o-photo')
                        ->color('info')
                        ->modalHeading(__('admin/partnerBill.fields.completion_photo'))
                        ->modalContent(fn(PartnerBill $record) => view('filament.admin.modals.completion-photo', [
                            'media' => $record->getFirstMedia('completion_photo'),
                        ]))
                        ->modalWidth('3xl')
                        ->slideOver()
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->visible(fn(PartnerBill $record): bool => $record->getFirstMedia('completion_photo') !== null),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
