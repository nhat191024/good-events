<?php

namespace App\Filament\Admin\Resources\EventOrganizationGuides\Schemas;

use App\Models\Category;
use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;

class EventOrganizationGuideForm
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
                            ->whereType('event_organization_guide')
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->getSearchResultsUsing(
                        fn(string $search): array =>
                        Category::query()
                            ->whereType('event_organization_guide')
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
