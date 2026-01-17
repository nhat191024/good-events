<?php

namespace App\Filament\Admin\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;

use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

use App\Models\User;
use Filament\Actions\DeleteAction;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('avatar')
                    ->label(__('admin/user.fields.label.avatar'))
                    ->formatStateUsing(function ($state, User $record) {
                        if ($record->avatar) {
                            $url = asset($record->avatar);
                            return '<img src="' . e($url) . '" alt="Avatar" style="height:80px;max-width:80px;object-fit:contain;border-radius:100px;">';
                        }
                        return '';
                    })
                    ->html(),
                TextColumn::make('name')
                    ->label(__('admin/user.fields.label.name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('admin/user.fields.label.email'))
                    ->searchable(),
                TextColumn::make('country_code')
                    ->label(__('admin/user.fields.label.country_code'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('admin/user.fields.label.phone'))
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->label(__('admin/user.fields.label.email_verified_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('admin/action.delete_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin/action.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin/action.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->default('trashed'),
            ])
            ->recordActions([
                // EditAction::make(),
                DeleteAction::make()
                    ->label(__('global.ban'))
                    ->modalHeading(__('admin/user.ban_title'))
                    ->modalDescription(__('admin/user.ban_description'))
                    ->modalSubmitActionLabel(__('global.ban'))
                    ->successNotificationTitle(__('admin/user.ban_success_message')),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
