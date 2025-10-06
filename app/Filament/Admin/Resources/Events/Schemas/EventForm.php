<?php

namespace App\Filament\Admin\Resources\Events\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin/event.fields.name'))
                    ->required(),
            ]);
    }
}
