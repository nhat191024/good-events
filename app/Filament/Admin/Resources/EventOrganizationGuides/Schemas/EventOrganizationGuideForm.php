<?php

namespace App\Filament\Admin\Resources\EventOrganizationGuides\Schemas;

use App\Models\Category;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;

use Cohensive\OEmbed\Facades\OEmbed;

class EventOrganizationGuideForm
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
                        TextInput::make('title')
                            ->label(__('admin/blog.fields.title'))
                            ->placeholder(__('admin/blog.placeholders.title'))
                            ->required(),
                        Grid::make(2)
                            ->schema([
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
                            ]),
                        SpatieTagsInput::make('tags')
                            ->label(__('admin/blog.fields.tags'))
                            ->placeholder(__('admin/blog.placeholders.tags'))
                            ->required(),
                        TextInput::make('video_url')
                            ->label(__('admin/blog.fields.video_url'))
                            ->placeholder(__('admin/blog.placeholders.video_url'))
                            ->helperText(__('admin/blog.helpers.video_url'))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                if ($state) {
                                    try {
                                        if (str_contains($state, '/shorts/')) {
                                            $state = strtok($state, '?');
                                            $state = str_replace('/shorts/', '/watch?v=', $state);
                                        }

                                        if (!str_contains($state, 'www.') && str_contains($state, 'youtube.com')) {
                                            $state = str_replace('youtube.com', 'www.youtube.com', $state);
                                        }

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
