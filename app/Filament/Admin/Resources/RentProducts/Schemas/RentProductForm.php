<?php

namespace App\Filament\Admin\Resources\RentProducts\Schemas;

use App\Models\Category;

use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;

class RentProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label(__('admin/rentProduct.fields.category'))
                    ->searchable()
                    ->getSearchResultsUsing(
                        fn(string $search): array =>
                        Category::query()
                            ->whereNotNull('parent_id')
                            ->where('name', 'like', "%{$search}%")
                            ->limit(50)
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->getOptionLabelUsing(
                        fn($value): ?string =>
                        Category::find($value)?->name
                    )
                    ->required(),
                TextInput::make('name')
                    ->label(__('admin/rentProduct.fields.name'))
                    ->placeholder(__('admin/rentProduct.placeholders.name'))
                    ->required(),
                TextInput::make('slug')
                    ->label(__('admin/rentProduct.fields.slug'))
                    ->placeholder(__('admin/rentProduct.placeholders.slug'))
                    ->disabled(),
                TextInput::make('price')
                    ->label(__('admin/rentProduct.fields.price'))
                    ->placeholder(__('admin/rentProduct.placeholders.price'))
                    ->numeric()
                    ->prefix('₫'),
                SpatieTagsInput::make('tags')
                    ->label(__('admin/rentProduct.fields.tags'))
                    ->placeholder(__('admin/rentProduct.placeholders.tags'))
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label(__('admin/rentProduct.fields.description'))
                    ->placeholder(__('admin/rentProduct.placeholders.description'))
                    ->required()
                    ->columnSpanFull(),
                SpatieMediaLibraryFileUpload::make('thumbnails')
                    ->label(__('admin/rentProduct.fields.image'))
                    ->collection('thumbnails')
                    ->required()
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->columnSpanFull(),
            ]);
    }
}
