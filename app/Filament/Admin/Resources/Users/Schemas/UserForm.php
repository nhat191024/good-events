<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar')
                    ->label(__('admin/user.fields.label.avatar'))
                    ->avatar()
                    ->directory('uploads/avatars')
                    ->columnSpanFull()
                    ->required(),
                TextInput::make('name')
                    ->label(__('admin/user.fields.label.name'))
                    ->placeholder(__('admin/user.fields.placeholder.name'))
                    ->required(),
                TextInput::make('email')
                    ->label(__('admin/user.fields.label.email'))
                    ->placeholder(__('admin/user.fields.placeholder.email'))
                    ->email()
                    ->required(),
                TextInput::make('country_code')
                    ->label(__('admin/user.fields.label.country_code'))
                    ->placeholder(__('admin/user.fields.placeholder.country_code'))
                    ->required(),
                TextInput::make('phone')
                    ->label(__('admin/user.fields.label.phone'))
                    ->placeholder(__('admin/user.fields.placeholder.phone'))
                    ->tel()
                    ->required(),
                TextInput::make('password')
                    ->label(__('admin/user.fields.label.password'))
                    ->placeholder(__('admin/user.fields.placeholder.password'))
                    ->password()
                    ->required(),
            ]);
    }
}
