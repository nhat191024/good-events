<?php

namespace App\Filament\Admin\Resources\Tags\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name.vi')
                    ->label(__('admin/tag.name'))
                    ->required(),
            ]);
    }
}
