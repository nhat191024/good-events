<?php

namespace App\Filament\Admin\Resources\RentProducts\Tables;

use Filament\Actions\EditAction;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SpatieTagsColumn;

use Filament\Tables\Filters\TrashedFilter;

class RentProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin/rentProduct.fields.name'))
                    ->searchable(),
                TextColumn::make('slug')
                    ->label(__('admin/rentProduct.fields.slug')),
                TextColumn::make('price')
                    ->label(__('admin/rentProduct.fields.price'))
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label(__('admin/rentProduct.fields.category'))
                    ->searchable(),
                SpatieTagsColumn::make('tags')
                    ->label(__('admin/rentProduct.fields.tags'))
                    ->alignCenter(),
                TextColumn::make('description')
                    ->label(__('admin/rentProduct.fields.description'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('admin/rentProduct.fields.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin/rentProduct.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin/rentProduct.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->native(false)
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    // ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
