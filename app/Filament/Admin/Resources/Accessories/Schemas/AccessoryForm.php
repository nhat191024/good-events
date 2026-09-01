<?php

namespace App\Filament\Admin\Resources\Accessories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AccessoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin/accessory.fields.name'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
            ]);
    }
}
