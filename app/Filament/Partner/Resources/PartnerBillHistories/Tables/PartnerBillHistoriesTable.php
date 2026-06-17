<?php

namespace App\Filament\Partner\Resources\PartnerBillHistories\Tables;

use App\Models\PartnerBill;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

use Filament\Actions\Action;

use App\Enum\PartnerBillStatus;

use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class PartnerBillHistoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->toggleable(),
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
                    ->label(__('partner/bill.phone')),
                TextColumn::make('address')
                    ->label(__('partner/bill.address'))
                    ->toggleable(),
                TextColumn::make('note')
                    ->label(__('partner/bill.note'))
                    ->toggleable(isToggledHiddenByDefault: true),
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
                DateRangeFilter::make('created_at')
                    ->label(__('partner/bill.filter_date')),
                SelectFilter::make('status')
                    ->label(__('partner/bill.filter_status'))
                    ->options(PartnerBillStatus::asSelectArray()),
            ])
            ->recordActions([
                Action::make('viewReview')
                    ->label('Xem đánh giá')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (PartnerBill $record): bool => (bool) $record->review_exists)
                    ->modalHeading(fn (PartnerBill $record): string => "Đánh giá đơn {$record->code}")
                    ->modalWidth('lg')
                    ->modalContent(function (PartnerBill $record) {
                        $review = $record->review()
                            ->with('ratings')
                            ->first();

                        $ratings = $review?->ratings->mapWithKeys(fn ($rating) => [
                            $rating->key => (int) $rating->value,
                        ]) ?? collect();

                        return view('filament.partner.modals.bill-review', [
                            'review' => $review,
                            'rating' => $ratings->get('rating') ?? $ratings->get('overall'),
                            'ratings' => $ratings,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Đóng'),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
