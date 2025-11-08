<?php

namespace App\Filament\Admin\Resources\BlogCategories\Resources\BlogCategoryChildrens\Tables;

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


class BlogCategoryChildrensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')
                    ->label(__('admin/category.fields.order')),
                SpatieMediaLibraryImageColumn::make('media')
                    ->label(__('admin/category.fields.image'))
                    ->collection('images')
                    ->circular()
                    ->imageSize(60),
                TextColumn::make('name')
                    ->label(__('admin/category.fields.name'))
                    ->searchable(),
                TextColumn::make('slug')
                    ->label(__('admin/category.fields.slug')),
                TextColumn::make('children_exists')
                    ->label(__('admin/category.fields.children'))
                    ->state(fn($record) => $record->children_exists)
                    ->formatStateUsing(fn($state): string => $state ? __('global.exists') : __('global.no'))
                    ->color(fn($state): string => $state ? 'success' : 'danger')
                    ->badge(),
                TextColumn::make('description')
                    ->label(__('admin/category.fields.description'))
                    ->html()
                    ->limit(50),
                TextColumn::make('deleted_at')
                    ->label(__('admin/category.fields.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin/category.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin/category.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->default('trashed')
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn($record): bool => $record->deleted_at === null),
                DeleteAction::make()
                    ->label(__('global.hidden'))
                    ->visible(fn($record): bool => $record->deleted_at === null),
                RestoreAction::make()
                    ->label(__('global.restore')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('global.hidden')),
                    RestoreBulkAction::make()
                        ->label(__('global.restore')),
                ]),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order');
    }
}
