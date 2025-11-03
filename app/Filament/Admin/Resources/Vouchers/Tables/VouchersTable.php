<?php

namespace App\Filament\Admin\Resources\Vouchers\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\DeleteAction;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;

class VouchersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('admin/voucher.fields.label.code'))
                    ->searchable(),
                TextColumn::make('model_type')
                    ->label(__('admin/voucher.fields.label.model_type'))
                    ->formatStateUsing(fn($state) => match ($state) {
                        'App\Models\PartnerProfile' => __('admin/voucher.fields.select.model_type.partner'),
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('data.discount_percent')
                    ->label(__('admin/voucher.fields.label.discount_percent'))
                    ->formatStateUsing(fn($state) => $state . '%')
                    ->sortable(),
                TextColumn::make('data.max_discount_amount')
                    ->label(__('admin/voucher.fields.label.max_discount_amount'))
                    ->formatStateUsing(fn($state) => '₫' . number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('data.min_order_amount')
                    ->label(__('admin/voucher.fields.label.min_order_amount'))
                    ->formatStateUsing(fn($state) =>  '₫' . number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('data.usage_limit')
                    ->label(__('admin/voucher.fields.label.usage_limit'))
                    ->sortable(),
                TextColumn::make('data.times_used')
                    ->label(__('admin/voucher.fields.label.times_used'))
                    ->sortable(),
                TextColumn::make('data.is_unlimited')
                    ->label(__('admin/voucher.fields.label.is_unlimited'))
                    ->formatStateUsing(fn($state) => $state ? __('global.yes') : __('global.no'))
                    ->sortable(),
                TextColumn::make('data.starts_at')
                    ->label(__('admin/voucher.fields.label.starts_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label(__('admin/voucher.fields.label.expires_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin/voucher.fields.label.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin/voucher.fields.label.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                ForceDeleteAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
