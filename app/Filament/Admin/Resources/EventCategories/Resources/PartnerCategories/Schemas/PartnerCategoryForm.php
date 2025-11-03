<?php

namespace App\Filament\Admin\Resources\EventCategories\Resources\PartnerCategories\Schemas;

use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class PartnerCategoryForm
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
                TextInput::make('min_price')
                    ->label(__('admin/partnerCategory.fields.min_price'))
                    ->numeric()
                    ->required(),
                TextInput::make('max_price')
                    ->label(__('admin/partnerCategory.fields.max_price'))
                    ->numeric()
                    ->required(),
                SpatieMediaLibraryFileUpload::make('images')
                    ->label(__('admin/partnerCategory.fields.image'))
                    ->collection('images')
                    ->image()
                    ->maxFiles(1)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label(__('admin/partnerCategory.fields.description'))
                    ->columnSpanFull(),
            ]);
    }
}
