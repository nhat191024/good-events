<?php

namespace App\Filament\Admin\Resources\Cities\Resources\Locations\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;

use Filament\Actions\BulkActionGroup;
// use Filament\Actions\DeleteBulkAction;
// use Filament\Actions\RestoreBulkAction;
// use Filament\Actions\ForceDeleteBulkAction;

class LocationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin/location.fields.name'))
                    ->searchable(),
                TextColumn::make('code')
                    ->label(__('admin/location.fields.code'))
                    ->searchable(),
                TextColumn::make('codename')
                    ->label(__('admin/location.fields.codename'))
                    ->searchable(),
                TextColumn::make('short_codename')
                    ->label(__('admin/location.fields.short_codename'))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('admin/location.fields.type'))
                    ->searchable(),
                // TextColumn::make('phone_code')
                //     ->label(__('admin/location.fields.phone_code'))
                //     ->searchable(),
                // TextColumn::make('parent_id')
                //     ->label(__('admin/location.fields.parent_id'))
                //     ->numeric()
                //     ->sortable(),
                TextColumn::make('deleted_at')
                    ->label(__('admin/location.fields.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin/location.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin/location.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // TrashedFilter::make(),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label(__('global.hidden')),
                // ForceDeleteAction::make()
                //     ->label(__('global.delete'))
                //     ->disabled(fn($record) => $record->wards_exists)
                //     ->tooltip(fn($record) => $record->wards_exists ? __('admin/location.cannot_delete_has_wards') : null),
                RestoreAction::make()
                    ->label(__('global.show')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                    // ForceDeleteBulkAction::make(),
                    // RestoreBulkAction::make(),
                ]),
            ]);
    }
}
