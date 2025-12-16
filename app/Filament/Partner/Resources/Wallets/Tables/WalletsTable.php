<?php

namespace App\Filament\Partner\Resources\Wallets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use App\Enum\TransactionType;

class WalletsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('partner/transaction.label.id')),
                TextColumn::make('meta.new_balance')
                    ->label(__('partner/transaction.label.balance'))
                    ->money('vnd', true),
                TextColumn::make('meta.old_balance')
                    ->label(__('partner/transaction.label.old_balance'))
                    ->money('vnd', true),
                TextColumn::make('amount')
                    ->label(__('partner/transaction.label.amount'))
                    ->money('vnd', true),
                TextColumn::make('type')
                    ->label(__('partner/transaction.label.type'))
                    ->badge()
                    ->formatStateUsing(fn($state) => TransactionType::from($state)->label())
                    ->colors([
                        'success' => TransactionType::DEPOSIT,
                        'danger' => TransactionType::WITHDRAW,
                    ]),
                TextColumn::make('meta.reason')
                    ->label(__('partner/transaction.label.reason')),
                TextColumn::make('created_at')
                    ->label(__('partner/transaction.label.created_at'))
                    ->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('partner/transaction.label.type'))
                    ->options([
                        TransactionType::DEPOSIT->value => TransactionType::DEPOSIT->label(),
                        TransactionType::WITHDRAW->value => TransactionType::WITHDRAW->label(),
                    ])
                    ->native(true),
            ])
            ->recordActions([
                // EditAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
