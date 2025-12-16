<?php

namespace App\Filament\Admin\Resources\Admins\Schemas;

use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;

use App\Enum\Role;

class AdminsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar')
                    ->label(__('admin/admin.fields.label.avatar'))
                    ->avatar()
                    ->directory('uploads/avatars')
                    ->columnSpanFull()
                    ->alignCenter(),
                TextInput::make('name')
                    ->label(__('admin/admin.fields.label.name'))
                    ->placeholder(__('admin/admin.fields.placeholder.name'))
                    ->columnSpanFull()
                    ->required(),
                TextInput::make('email')
                    ->email()
                    ->label(__('admin/admin.fields.label.email'))
                    ->placeholder(__('admin/admin.fields.placeholder.email'))
                    ->helperText(__('admin/admin.fields.helper.email'))
                    ->columnSpanFull()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->label(__('admin/admin.fields.label.password'))
                    ->placeholder(__('admin/admin.fields.placeholder.password'))
                    ->helperText(__('admin/admin.fields.helper.password'))
                    ->columnSpanFull()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn ($state) => filled($state)),
                Select::make('role')
                    ->label(__('admin/admin.fields.label.role'))
                    ->options([
                        Role::ADMIN->value => Role::ADMIN->label(),
                        Role::HUMAN_RESOURCE_MANAGER->value => Role::HUMAN_RESOURCE_MANAGER->label(),
                        Role::DESIGN_MANAGER->value => Role::DESIGN_MANAGER->label(),
                        Role::RENTAL_MANAGER->value => Role::RENTAL_MANAGER->label(),
                    ])
                    ->afterStateHydrated(function (Select $component, $record) {
                        if ($record && $record->roles->isNotEmpty()) {
                            $component->state($record->roles->first()->name);
                        }
                    })
                    ->helperText(__('admin/admin.fields.helper.role'))
                    ->columnSpanFull()
                    ->native(false)
                    ->required(),
            ]);
    }
}
