<?php

namespace App\Filament\Admin\Resources\BlogCategories\Schemas;

use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

use App\Enum\CategoryType;

class BlogCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin/category.fields.name'))
                    ->placeholder(__('admin/category.placeholders.name'))
                    ->required(),
                Select::make('type')
                    ->label(__('admin/category.fields.type'))
                    ->options([
                        CategoryType::GOOD_LOCATION->value => __('admin/category.category_options.good_location'),
                        CategoryType::VOCATIONAL_KNOWLEDGE->value => __('admin/category.category_options.vocational_knowledge'),
                        CategoryType::EVENT_ORGANIZATION_GUIDE->value => __('admin/category.category_options.event_organization_guide'),
                    ])
                    ->native(false)
                    ->required(),
                TextInput::make('slug')
                    ->label(__('admin/category.fields.slug'))
                    ->placeholder(__('admin/category.placeholders.slug'))
                    ->columnSpanFull()
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
