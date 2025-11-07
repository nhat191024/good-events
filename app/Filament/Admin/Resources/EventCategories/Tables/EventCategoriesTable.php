<?php

namespace App\Filament\Admin\Resources\EventCategories\Tables;

use App\Filament\Admin\Resources\EventCategories\EventCategoryResource;

use App\Models\PartnerCategory;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
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

class EventCategoriesTable
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
                TextColumn::make('children_exists')
                    ->label(__('admin/partnerCategory.fields.partner_category'))
                    ->state(fn($record) => $record->children_exists)
                    ->formatStateUsing(fn($state): string => $state ? __('global.exists') : __('global.no'))
                    ->color(fn($state): string => $state ? 'success' : 'danger')
                    ->badge(),
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
                Action::make('manage-partner-categories')
                    ->label(__('admin/partnerCategory.manage_child_partner_categories'))
                    ->url(fn($record): string => EventCategoryResource::getUrl('partner-categories', ['record' => $record->id]))
                    ->visible(fn($record): bool => $record->deleted_at === null),
                EditAction::make()
                    ->visible(fn($record): bool => $record->deleted_at === null),
                DeleteAction::make()
                    ->label(__('global.hidden'))
                    ->visible(fn($record): bool => $record->deleted_at === null)
                    ->disabled(fn($record): bool => $record->children_exists)
                    ->tooltip(fn($record): ?string => $record->children_exists ? __('admin/partnerCategory.cannot_hidden_category_has_services') : null),
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
            ->reorderable('order')
        ;
    }
}
