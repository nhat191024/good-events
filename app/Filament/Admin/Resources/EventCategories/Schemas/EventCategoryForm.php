<?php

namespace App\Filament\Admin\Resources\EventCategories\Schemas;

use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

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
                SpatieMediaLibraryFileUpload::make('images')
                    ->label(__('admin/partnerCategory.fields.image'))
                    ->collection('images')
                    ->image()
                    ->maxFiles(1)
                    ->columnSpanFull(),
            RichEditor::make('description')
                    ->label(__('admin/partnerCategory.fields.description'))
                    ->columnSpanFull(),
            ]);
    }
}
