<?php

namespace App\Filament\Admin\Resources\Admins\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;

use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\CreateAction;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

use App\Models\User;
use App\Enum\Role;
use Filament\Actions\DeleteAction;


class AdminsTable
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
                TextColumn::make('roles.name')
                    ->label(__('admin/admin.fields.label.role'))
                    ->formatStateUsing(function ($state) {
                        return Role::tryFrom($state)?->label() ?? ucfirst($state);
                    })
                    ->badge()
                    ->sortable(),
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
                EditAction::make()
                    ->after(function (User $record, array $data) {
                        if (isset($data['role'])) {
                            $record->syncRoles([$data['role']]);
                        }
                    }),
                DeleteAction::make()
                    ->label(__('global.ban'))
                    ->modalHeading(__('admin/user.ban_title'))
                    ->modalDescription(__('admin/user.ban_description'))
                    ->modalSubmitActionLabel(__('global.ban'))
                    ->successNotificationTitle(__('admin/user.ban_success_message'))
                    ->visible(fn(?User $record): bool => auth()->id() !== $record?->id)
                    ->before(function (User $record): void {
                        if (auth()->id() === $record->id) {
                            abort(403, __('admin/user.cannot_ban_self'));
                        }
                    }),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
