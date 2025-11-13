<?php

namespace App\Filament\Admin\Resources\EventCategories\Resources\PartnerCategories\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Filters\TrashedFilter;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;

class PartnerCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')
                    ->label(__('admin/partnerCategory.fields.order')),
                SpatieMediaLibraryImageColumn::make('media')
                    ->label(__('admin/partnerCategory.fields.image'))
                    ->collection('images')
                    ->circular()
                    ->imageSize(60),
                // ->conversion('thumb'),
                TextColumn::make('name')
                    ->label(__('admin/partnerCategory.fields.name'))
                    ->searchable(),
                TextColumn::make('slug')
                    ->label(__('admin/partnerCategory.fields.slug'))
                    ->searchable(),
                TextColumn::make('min_price')
                    ->label(__('admin/partnerCategory.fields.min_price'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_price')
                    ->label(__('admin/partnerCategory.fields.max_price'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('admin/partnerCategory.fields.description'))
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('admin/partnerCategory.fields.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin/partnerCategory.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin/partnerCategory.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn($record): bool => $record->deleted_at === null),
                DeleteAction::make()
                    ->label(__('global.hidden'))
                    ->visible(fn($record): bool => $record->deleted_at === null)
                    ->disabled(fn($record): bool => (bool) $record->partner_services_exists)
                    ->tooltip(fn($record): ?string => $record->partner_services_exists ? __('admin/partnerCategory.cannot_hidden_partner_category_has_services') : null),
                RestoreAction::make()
                    ->visible(fn($record): bool => $record->deleted_at !== null)
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order');
    }
}
