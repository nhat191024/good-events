<?php

namespace App\Filament\Admin\Resources\VocationalKnowledge\Schemas;

use App\Models\Category;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;

use Cohensive\OEmbed\Facades\OEmbed;

class VocationalKnowledgeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make(__('admin/blog.sections.basic_info'))
                    ->description(__('admin/blog.sections.basic_info_description'))
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label(__('admin/blog.fields.title'))
                                    ->placeholder(__('admin/blog.placeholders.title'))
                                    ->required(),
                                TextInput::make('slug')
                                    ->label(__('admin/blog.fields.slug'))
                                    ->placeholder(__('admin/blog.placeholders.slug'))
                                    ->disabled(),
                                TextInput::make('video_url')
                                    ->label(__('admin/blog.fields.video_url'))
                                    ->placeholder(__('admin/blog.placeholders.video_url'))
                                    ->helperText(__('admin/blog.helpers.video_url'))
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                                        if ($state) {
                                            try {
                                                $embed = OEmbed::get($state);
                                                if ($embed) {
                                                    $set('video_url', $embed->html([
                                                        'width' => 640,
                                                        'height' => 360,
                                                    ]));
                                                } else {
                                                    $set('video_url', null);
                                                }
                                            } catch (\Exception $e) {
                                                $set('video_url', null);
                                            }
                                        } else {
                                            $set('video_url', null);
                                        }
                                    })
                                    ->dehydrateStateUsing(function (?string $state): ?string {
                                        if ($state && !str_starts_with($state, '<iframe') || str_starts_with($state, '<blockquote')) {
                                            try {
                                                $embed = OEmbed::get($state);
                                                if ($embed) {
                                                    return $embed->html([
                                                        'width' => 640,
                                                        'height' => 360,
                                                    ]);
                                                }
                                            } catch (\Exception $e) {
                                                return null;
                                            }
                                        }
                                        return $state;
                                    }),
                                Select::make('category_id')
                                    ->label(__('admin/blog.fields.category_id'))
                                    ->searchable()
                                    ->options(
                                        fn() => Category::query()
                                            ->whereType('vocational_knowledge')
                                            ->orderBy('created_at', 'desc')
                                            ->limit(10)
                                            ->pluck('name', 'id')
                                            ->toArray()
                                    )
                                    ->getSearchResultsUsing(
                                        fn(string $search): array =>
                                        Category::query()
                                            ->whereType('vocational_knowledge')
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
                            ]),
                        SpatieTagsInput::make('tags')
                            ->label(__('admin/blog.fields.tags'))
                            ->placeholder(__('admin/blog.placeholders.tags'))
                            ->required(),
                    ])
                    ->columnSpanFull(),

                Section::make(__('admin/blog.sections.content'))
                    ->description(__('admin/blog.sections.content_description'))
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->schema([
                        RichEditor::make('content')
                            ->label(__('admin/blog.fields.content'))
                            ->placeholder(__('admin/blog.placeholders.content'))
                            ->required(),
                    ])
                    ->columnSpanFull(),

                Section::make(__('admin/blog.sections.media'))
                    ->description(__('admin/blog.sections.media_description'))
                    ->icon('heroicon-o-photo')
                    ->collapsible()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('images')
                            ->label(__('admin/blog.fields.thumbnail'))
                            ->collection('thumbnail')
                            ->required()
                            ->image()
                            ->imageEditor()
                            ->maxFiles(1),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
