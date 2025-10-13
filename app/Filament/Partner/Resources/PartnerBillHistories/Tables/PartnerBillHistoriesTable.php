<?php

namespace App\Filament\Partner\Resources\PartnerBillHistories\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

use App\Enum\PartnerBillStatus;

class PartnerBillHistoriesTable
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
                    ->searchable(),
                TextColumn::make('event.name')
                    ->label(__('partner/bill.event'))
                    ->searchable(),
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
                    ->time(),
                TextColumn::make('phone')
                    ->label(__('partner/bill.phone'))
                    ->searchable(),
                TextColumn::make('address')
                    ->label(__('partner/bill.address'))
                    ->searchable(),
                TextColumn::make('final_total')
                    ->label(__('partner/bill.final_total'))
                    ->numeric()
                    ->formatStateUsing(fn($state) => number_format($state) . ' VND'),
                TextColumn::make('status')
                    ->label(__('partner/bill.status'))
                    ->badge()
                    ->formatStateUsing(fn($state) => $state->label())
                    ->colors([
                        'primary' => PartnerBillStatus::PENDING,
                        'success' => PartnerBillStatus::COMPLETED,
                        'danger' => PartnerBillStatus::CANCELLED,
                        'warning' => PartnerBillStatus::CONFIRMED,
                        'info' => PartnerBillStatus::IN_JOB,
                    ])
                    ->searchable(),
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
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}
