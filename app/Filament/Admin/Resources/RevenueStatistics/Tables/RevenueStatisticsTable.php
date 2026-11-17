<?php

namespace App\Filament\Admin\Resources\RevenueStatistics\Tables;

use App\Enum\PaymentMethod;
use App\Models\RevenueRecord;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class RevenueStatisticsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label(__('admin/revenue_statistic.fields.invoice_number'))
                    ->copyable(),
                TextColumn::make('source')
                    ->label(__('admin/revenue_statistic.fields.source'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::sourceOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => $state === RevenueRecord::SOURCE_ASSET ? 'info' : 'success')
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label(__('admin/revenue_statistic.fields.paid_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('reference_code')
                    ->label(__('admin/revenue_statistic.fields.reference_code'))
                    ->copyable(),
                TextColumn::make('buyer_name')
                    ->label(__('admin/revenue_statistic.fields.buyer'))
                    ->description(fn (RevenueRecord $record): ?string => $record->buyer_email)
                    ->searchable(['buyer_name', 'buyer_email', 'buyer_phone'])
                    ->sortable(),
                TextColumn::make('buyer_phone')
                    ->label(__('admin/revenue_statistic.fields.buyer_phone'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('description')
                    ->label(__('admin/revenue_statistic.fields.description'))
                    ->wrap()
                    ->searchable(),
                TextColumn::make('subtotal')
                    ->label(__('admin/revenue_statistic.fields.subtotal'))
                    ->money('VND')
                    ->sortable()
                    ->summarize(Sum::make()->money('VND')->label(__('admin/revenue_statistic.summary.total'))),
                TextColumn::make('vat_rate')
                    ->label(__('admin/revenue_statistic.fields.vat_rate'))
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('vat_amount')
                    ->label(__('admin/revenue_statistic.fields.vat_amount'))
                    ->money('VND')
                    ->sortable()
                    ->summarize(Sum::make()->money('VND')->label(__('admin/revenue_statistic.summary.total'))),
                TextColumn::make('total')
                    ->label(__('admin/revenue_statistic.fields.total'))
                    ->money('VND')
                    ->weight('bold')
                    ->sortable()
                    ->summarize(Sum::make()->money('VND')->label(__('admin/revenue_statistic.summary.total'))),
                TextColumn::make('payment_method')
                    ->label(__('admin/revenue_statistic.fields.payment_method'))
                    ->formatStateUsing(fn (?string $state): ?string => PaymentMethod::tryFrom((string) $state)?->label() ?? $state)
                    ->toggleable(),
                TextColumn::make('order_code')
                    ->label(__('admin/revenue_statistic.fields.order_code'))
                    ->placeholder('—')
                    ->copyable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('bank_reference')
                    ->label(__('admin/revenue_statistic.fields.bank_reference'))
                    ->placeholder('—')
                    ->copyable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('gateway_time')
                    ->label(__('admin/revenue_statistic.fields.gateway_time'))
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('payment_reference')
                    ->label(__('admin/revenue_statistic.fields.payment_reference'))
                    ->placeholder('—')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin/revenue_statistic.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->label(__('admin/revenue_statistic.fields.source'))
                    ->options(self::sourceOptions())
                    ->native(false),
                DateRangeFilter::make('paid_at')
                    ->label(__('admin/revenue_statistic.fields.paid_at')),
            ])
            ->defaultSort('paid_at', 'desc');
    }

    /**
     * @return array<string, string>
     */
    public static function sourceOptions(): array
    {
        return [
            RevenueRecord::SOURCE_DEPOSIT => __('admin/revenue_statistic.source.deposit'),
            RevenueRecord::SOURCE_ASSET => __('admin/revenue_statistic.source.asset'),
        ];
    }
}
