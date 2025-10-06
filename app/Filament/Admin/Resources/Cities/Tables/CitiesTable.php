<?php

namespace App\Filament\Admin\Resources\Cities\Tables;

use App\Filament\Admin\Resources\Cities\CityResource;

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

class CitiesTable
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
                // TextColumn::make('short_codename')
                //     ->label(__('admin/location.fields.short_codename'))
                //     ->searchable(),
                TextColumn::make('type')
                    ->label(__('admin/location.fields.type'))
                    ->searchable(),
                TextColumn::make('phone_code')
                    ->label(__('admin/location.fields.phone_code'))
                    ->searchable(),
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
                SelectFilter::make('type')
                    ->label(__('admin/location.fields.type'))
                    ->options([
                        'tỉnh' => __('admin/location.filters.province'),
                        'thành phố trung ương' => __('admin/location.filters.city'),
                        'đặc khu' => __('admin/location.filters.special_zone'),
                        'phường' => __('admin/location.filters.ward'),
                        'xã' => __('admin/location.filters.commune'),
                    ])
                    ->placeholder(__('admin/location.placeholders.type'))
                    ->native(false),
            ])
            ->recordActions([
                Action::make('manage-wards')
                    ->label(__('admin/location.actions.manage_wards'))
                    ->url(fn($record): string => CityResource::getUrl('wards', ['record' => $record->id]))
                    ->visible(fn($record) => $record->deleted_at === null),
                DeleteAction::make()
                    ->label(__('global.hidden'))
                    ->disabled(fn($record) => $record->wards_exists)
                    ->tooltip(fn($record) => $record->wards_exists ? __('admin/location.cannot_hidden_has_wards') : null),
                ForceDeleteAction::make()
                    ->label(__('global.delete'))
                    ->disabled(fn($record) => $record->wards_exists)
                    ->tooltip(fn($record) => $record->wards_exists ? __('admin/location.cannot_delete_has_wards') : null),
                RestoreAction::make()
                    ->label(__('global.show')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
