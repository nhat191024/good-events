<?php

namespace App\Filament\Admin\Resources\DesignCategories\Resources\DesignCategoryChildrens\Schemas;

use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class DesignCategoryChildrenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin/category.fields.name'))
                    ->placeholder(__('admin/category.placeholders.name'))
                    ->required(),
                TextInput::make('slug')
                    ->label(__('admin/category.fields.slug'))
                    ->placeholder(__('admin/category.placeholders.slug'))
                    ->disabled(),
                RichEditor::make('description')
                    ->label(__('admin/category.fields.description'))
                    ->placeholder(__('admin/category.placeholders.description'))
                    ->columnSpanFull(),
                SpatieMediaLibraryFileUpload::make('images')
                    ->label(__('admin/category.fields.image'))
                    ->collection('images')
                    ->image()
                    ->maxFiles(1)
                    ->columnSpanFull(),
            ]);
    }
}
