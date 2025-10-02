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
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('admin/partnerBill.fields.phone'))
                    ->searchable(),
                TextColumn::make('date')
                    ->label(__('admin/partnerBill.fields.date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label(__('admin/partnerBill.fields.start_time'))
                    ->time()
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label(__('admin/partnerBill.fields.end_time'))
                    ->time()
                    ->sortable(),
                TextColumn::make('final_total')
                    ->label(__('admin/partnerBill.fields.final_total'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('event.name')
                    ->label(__('admin/partnerBill.fields.event'))
                    ->searchable()
                    ->sortable(),
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
                        PartnerBillStatus::PAID => 'success',
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
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
