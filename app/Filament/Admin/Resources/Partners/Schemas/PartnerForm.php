<?php

namespace App\Filament\Admin\Resources\Partners\Schemas;

use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;

use Illuminate\Support\Facades\Hash;

class PartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar')
                    ->label(__('admin\partner.fields.label.avatar'))
                    ->avatar()
                    ->directory('uploads/avatars')
                    ->columnSpanFull()
                    ->dehydrated(fn($state) => filled($state))
                    ->dehydrateStateUsing(function ($state, $record) {
                        return filled($state) ? $state : ($record?->avatar ?? null);
                    }),
                TextInput::make('name')
                    ->label(__('admin\partner.fields.label.name'))
                    ->placeholder(__('admin\partner.fields.placeholder.name'))
                    ->required(),
                TextInput::make('partner_name')
                    ->label(__('admin\partner.fields.label.partner_name'))
                    ->placeholder(__('admin\partner.fields.placeholder.partner_name'))
                    ->required(),
                TextInput::make('email')
                    ->label(__('admin\partner.fields.label.email'))
                    ->placeholder(__('admin\partner.fields.placeholder.email'))
                    ->email()
                    ->required(),
                TextInput::make('country_code')
                    ->label(__('admin\partner.fields.label.country_code'))
                    ->placeholder(__('admin\partner.fields.placeholder.country_code'))
                    ->required(),
                TextInput::make('phone')
                    ->label(__('admin\partner.fields.label.phone'))
                    ->placeholder(__('admin\partner.fields.placeholder.phone'))
                    ->tel()
                    ->required(),
                TextInput::make('identity_card_number')
                    ->label(__('admin\partner.fields.label.identity_card_number'))
                    ->placeholder(__('admin\partner.fields.placeholder.identity_card_number'))
                    ->required(),
                TextInput::make('password')
                    ->label(__('admin\partner.fields.label.password'))
                    ->placeholder(__('admin\partner.fields.placeholder.password'))
                    ->password()
                    ->dehydrated(fn($state) => filled($state))
                    ->dehydrateStateUsing(fn($state) => Hash::make($state)),
            ]);
    }
}
