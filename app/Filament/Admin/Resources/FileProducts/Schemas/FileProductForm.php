<?php

namespace App\Filament\Admin\Resources\FileProducts\Schemas;

use App\Models\Category;

use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;

use RalphJSmit\Filament\Upload\Filament\Forms\Components\AdvancedFileUpload;

class FileProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label(__('admin/fileProduct.fields.category_id'))
                    ->searchable()
                    ->options(
                        fn() => Category::query()
                            ->whereType('design')
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->getSearchResultsUsing(
                        fn(string $search): array =>
                        Category::query()
                            ->whereType('design')
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
                    ->label(__('admin/fileProduct.fields.name'))
                    ->placeholder(__('admin/fileProduct.placeholders.name'))
                    ->required(),
                TextInput::make('slug')
                    ->label(__('admin/fileProduct.fields.slug'))
                    ->placeholder(__('admin/fileProduct.placeholders.slug'))
                    ->disabled(),
                TextInput::make('price')
                    ->label(__('admin/fileProduct.fields.price'))
                    ->placeholder(__('admin/fileProduct.placeholders.price'))
                    ->required()
                    ->numeric()
                    ->prefix('₫'),
                SpatieTagsInput::make('tags')
                    ->label(__('admin/fileProduct.fields.tags'))
                    ->placeholder(__('admin/fileProduct.placeholders.tags'))
                    ->required()
                    ->columnSpanFull(),
                RichEditor::make('description')
                    ->label(__('admin/fileProduct.fields.description'))
                    ->placeholder(__('admin/fileProduct.placeholders.description'))
                    ->required()
                    ->columnSpanFull(),
                AdvancedFileUpload::make('images')
                    ->label(__('admin/fileProduct.fields.thumbnail'))
                    ->helperText(__('admin/fileProduct.helpers.thumbnail'))
                    ->spatieMediaLibrary()
                    ->collection('thumbnails')
                    ->required()

                    ->multiple()
                    ->reorderable()

                    ->disk('local')
                    ->temporaryFileUploadDisk('local')

                    ->image()
                    ->maxFiles(5)
                    ->maxSize(1024 * 3)
                    ->columnSpanFull(),
            ]);
    }
}
