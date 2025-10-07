<?php

namespace App\Filament\Partner\Resources\PartnerBills\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
// use Filament\Tables\Filters\TrashedFilter;

// use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;

// use Filament\Actions\BulkActionGroup;
// use Filament\Actions\DeleteBulkAction;
// use Filament\Actions\RestoreBulkAction;
// use Filament\Actions\ForceDeleteBulkAction;

use App\Enum\PartnerBillStatus;

class PartnerBillsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('client.name')
                    ->label(__('partner/bill.client'))
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label(__('partner/bill.category'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('event.name')
                    ->label(__('partner/bill.event'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('date')
                    ->label(__('partner/bill.date'))
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label(__('partner/bill.start_time'))
                    ->badge()
                    ->color('success')
                    ->time(),
                TextColumn::make('end_time')
                    ->label(__('partner/bill.end_time'))
                    ->badge()
                    ->color('danger')
                    ->time()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')
                    ->label(__('partner/bill.phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('address')
                    ->label(__('partner/bill.address'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('final_total')
                    ->label(__('partner/bill.final_total'))
                    ->numeric()
                    ->formatStateUsing(fn($state) => number_format($state) . ' VND')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label(__('partner/bill.status'))
                    ->badge()
                    ->formatStateUsing(fn($state) => $state->label())
                    ->colors([
                        'primary' => PartnerBillStatus::PENDING,
                        'success' => PartnerBillStatus::COMPLETED,
                        'danger' => PartnerBillStatus::CANCELLED,
                    ])
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->reorderableColumns()
            ->deferColumnManager(false)
            ->recordActions([
                ViewAction::make()
                    ->label('Xem chi tiết'),
                // EditAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
