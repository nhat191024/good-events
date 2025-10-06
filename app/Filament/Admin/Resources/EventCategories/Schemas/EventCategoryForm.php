<?php

namespace App\Filament\Admin\Resources\EventCategories\Schemas;

use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class EventCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin/partnerCategory.fields.name'))
                    ->required(),
                TextInput::make('slug')
                    ->label(__('admin/partnerCategory.fields.slug'))
                    ->placeholder(__('admin/partnerCategory.placeholders.slug'))
                    ->disabled(),
                Textarea::make('description')
                    ->label(__('admin/partnerCategory.fields.description'))
                    ->columnSpanFull(),
            ]);
    }
}
