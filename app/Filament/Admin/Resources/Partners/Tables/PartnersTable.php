<?php

namespace App\Filament\Admin\Resources\Partners\Tables;

use App\Models\User;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;

// use Filament\Actions\BulkActionGroup;
// use Filament\Actions\DeleteBulkAction;
// use Filament\Actions\RestoreBulkAction;
// use Filament\Actions\ForceDeleteBulkAction;

class PartnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('avatar')
                    ->label(__('admin\partner.fields.label.avatar'))
                    ->formatStateUsing(function ($state, User $record) {
                        if ($record->avatar) {
                            $url = asset($record->avatar);
                            return '<img src="' . e($url) . '" alt="Avatar" style="height:80px;max-width:80px;object-fit:contain;border-radius:100px;">';
                        }
                        return '';
                    })
                    ->html(),
                TextColumn::make('name')
                    ->label(__('admin\partner.fields.label.name'))
                    ->searchable(),
                TextColumn::make('partnerProfile.partner_name')
                    ->label(__('admin\partner.fields.label.partner_name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('admin\partner.fields.label.email'))
                    ->searchable(),
                TextColumn::make('country_code')
                    ->label(__('admin\partner.fields.label.country_code'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('admin\partner.fields.label.phone'))
                    ->searchable(),
                TextColumn::make('partnerProfile.identity_card_number')
                    ->label(__('admin\partner.fields.label.identity_card_number'))
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->label(__('admin\partner.fields.label.email_verified_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('admin\action.delete_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin\action.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin\action.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
