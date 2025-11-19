<?php

namespace App\Filament\Admin\Resources\BlogCategories\Tables;

use App\Filament\Admin\Resources\BlogCategories\BlogCategoryResource;

use App\Enum\CategoryType;

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

class BlogCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
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
                TextColumn::make('type')
                    ->label(__('admin/category.fields.type'))
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        CategoryType::GOOD_LOCATION->value => __('admin/category.category_options.good_location'),
                        CategoryType::VOCATIONAL_KNOWLEDGE->value => __('admin/category.category_options.vocational_knowledge'),
                        CategoryType::EVENT_ORGANIZATION_GUIDE->value => __('admin/category.category_options.event_organization_guide'),
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('admin/category.fields.description'))
                    ->html()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Action::make('manage-children-categories')
                    ->label(__('admin/category.actions.manage_children_categories'))
                    ->url(fn($record): string => BlogCategoryResource::getUrl('children-categories', ['record' => $record->id]))
                    ->visible(fn($record): bool => $record->deleted_at === null),
                EditAction::make()
                    ->visible(fn($record): bool => $record->deleted_at === null),
                DeleteAction::make()
                    ->label(__('global.hidden'))
                    ->visible(fn($record): bool => $record->deleted_at === null)
                    ->disabled(fn($record): bool => $record->children_exists)
                    ->tooltip(fn($record): ?string => $record->children_exists ? __('admin/category.cannot_hidden_category_has_children') : null),
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
            ]);
    }
}
