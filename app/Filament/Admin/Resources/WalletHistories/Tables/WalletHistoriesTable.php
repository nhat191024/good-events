<?php

namespace App\Filament\Admin\Resources\WalletHistories\Tables;

use App\Enum\TransactionType;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WalletHistoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('partner/transaction.label.id'))
                    ->sortable(),
                TextColumn::make('payable.name')
                    ->label(__('admin/wallet_history.label.user'))
                    ->description(fn ($record): ?string => $record->payable?->email)
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->whereHasMorph('payable', '*', fn (Builder $q) => $q
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%"))),
                TextColumn::make('meta.old_balance')
                    ->label(__('partner/transaction.label.old_balance'))
                    ->money('vnd', true),
                TextColumn::make('amount')
                    ->label(__('partner/transaction.label.amount'))
                    ->money('vnd', true)
                    ->sortable(),
                TextColumn::make('meta.new_balance')
                    ->label(__('partner/transaction.label.balance'))
                    ->money('vnd', true),
                TextColumn::make('type')
                    ->label(__('partner/transaction.label.type'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => TransactionType::from($state)->label())
                    ->colors([
                        'success' => TransactionType::DEPOSIT,
                        'danger' => TransactionType::WITHDRAW,
                    ]),
                TextColumn::make('meta.reason')
                    ->label(__('partner/transaction.label.reason'))
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label(__('partner/transaction.label.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
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
            ->defaultSort('created_at', 'desc');
    }
}
