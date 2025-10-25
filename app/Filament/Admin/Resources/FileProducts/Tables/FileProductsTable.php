<?php

namespace App\Filament\Admin\Resources\FileProducts\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SpatieTagsColumn;

use Filament\Tables\Filters\TrashedFilter;

use Filament\Support\Enums\IconSize;

use App\Filament\Admin\Resources\FileProducts\FileProductResource;

class FileProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin/fileProduct.fields.name'))
                    ->searchable(),
                TextColumn::make('slug')
                    ->label(__('admin/fileProduct.fields.slug')),
                TextColumn::make('price')
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label(__('admin/fileProduct.fields.category_id'))
                    ->searchable(),
                SpatieTagsColumn::make('tags')
                    ->label(__('admin/fileProduct.fields.tags'))
                    ->alignCenter(),
                TextColumn::make('deleted_at')
                    ->label(__('admin/fileProduct.fields.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin/fileProduct.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin/fileProduct.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->native(false),
            ])
            ->recordActions([
                Action::make('manage_designs')
                    ->label('Quản lý ảnh')
                    ->icon('heroicon-o-photo')
                    ->iconSize(IconSize::Small)
                    ->color('info')
                    ->url(fn($record) => FileProductResource::getUrl('manage-designs', ['record' => $record])),
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
