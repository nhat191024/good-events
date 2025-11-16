<?php

namespace App\Filament\Admin\Resources\GoodLocations\Schemas;

use App\Models\Category;
use App\Models\Location;

use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Schemas\Components\Utilities\Get;

class GoodLocationsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label(__('admin/blog.fields.title'))
                    ->placeholder(__('admin/blog.placeholders.title'))
                    ->columnSpanFull()
                    ->required(),
                Select::make('category_id')
                    ->label(__('admin/blog.fields.category_id'))
                    ->searchable()
                    ->options(
                        fn() => Category::query()
                            ->where('type', 'good_location')
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->getSearchResultsUsing(
                        fn(string $search): array =>
                        Category::query()
                            ->where('type', 'good_location')
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
                TextInput::make('slug')
                    ->label(__('admin/blog.fields.slug'))
                    ->placeholder(__('admin/blog.placeholders.slug'))
                    ->disabled(),
                SpatieTagsInput::make('tags')
                    ->label(__('admin/blog.fields.tags'))
                    ->placeholder(__('admin/blog.placeholders.tags'))
                    ->required()
                    ->columnSpanFull(),
                Select::make('city_id')
                    ->label(__('admin/blog.fields.city'))
                    ->placeholder(__('admin/blog.placeholders.city'))
                    ->searchable()
                    ->live()
                    ->options(
                        fn() => Location::query()
                            ->whereNull('parent_id')
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->afterStateUpdated(fn(callable $set) => $set('location_id', null))
                    ->required(),
                Select::make('location_id')
                    ->label(__('admin/blog.fields.ward'))
                    ->placeholder(__('admin/blog.placeholders.ward'))
                    ->searchable()
                    ->options(
                        fn(Get $get): array => Location::query()
                            ->where('parent_id', $get('city_id'))
                            ->whereNotNull('parent_id')
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->disabled(fn(Get $get): bool => !$get('city_id'))
                    ->required(),
                RichEditor::make('content')
                    ->label(__('admin/blog.fields.content'))
                    ->placeholder(__('admin/blog.placeholders.content'))
                    ->columnSpanFull()
                    ->required(),
                SpatieMediaLibraryFileUpload::make('images')
                    ->label(__('admin/blog.fields.thumbnail'))
                    ->collection('thumbnail')
                    ->required()
                    ->image()
                    ->imageEditor()
                    ->maxFiles(1)
                    ->columnSpanFull(),
            ]);
    }
}
