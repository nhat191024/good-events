<?php

namespace App\Filament\Admin\Resources\Categories\Schemas;

use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class CategoryForm
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
                Textarea::make('description')
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
