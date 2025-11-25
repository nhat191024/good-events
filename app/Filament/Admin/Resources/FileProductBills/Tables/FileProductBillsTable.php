<?php

namespace App\Filament\Admin\Resources\FileProductBills\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;

use Filament\Actions\Action;
use Filament\Actions\EditAction;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;

use App\Enum\FileProductBillStatus;

use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class FileProductBillsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('admin/fileProductBill.fields.id'))
                    ->searchable(),
                TextColumn::make('fileProduct.name')
                    ->label(__('admin/fileProductBill.fields.file_product_id'))
                    ->searchable(),
                TextColumn::make('client.name')
                    ->label(__('admin/fileProductBill.fields.client_id'))
                    ->searchable(),
                TextColumn::make('final_total')
                    ->label(__('admin/fileProductBill.fields.final_total'))
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('status')
                    ->formatStateUsing(fn($state) => $state->label())
                    ->color(fn($state) => match ($state) {
                        FileProductBillStatus::PENDING => 'info',
                        FileProductBillStatus::CANCELLED => 'danger',
                        FileProductBillStatus::PAID => 'success',
                        default => 'secondary',
                    })
                    ->badge(),
                TextColumn::make('created_at')
                    ->label(__('admin/fileProductBill.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin/fileProductBill.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                DateRangeFilter::make('created_at')
                    ->label(__('admin/fileProductBill.fields.created_at')),
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
