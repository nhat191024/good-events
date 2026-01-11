<?php

namespace App\Filament\Admin\Resources\EventCategories\Resources\PartnerCategories\Schemas;

use App\Models\PartnerCategory;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

use Cohensive\OEmbed\Facades\OEmbed;

class PartnerCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin/partnerCategory.fields.name'))
                    ->required(),
                Select::make('parent_id')
                    ->label(__('admin/partnerCategory.fields.parent_id'))
                    ->searchable()
                    ->getSearchResultsUsing(
                        fn(string $search): array =>
                        PartnerCategory::query()
                            ->whereNull('parent_id')
                            ->where('name', 'like', "%{$search}%")
                            ->limit(50)
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->getOptionLabelUsing(
                        callback: fn($value): ?string =>
                        PartnerCategory::find($value)?->name
                    )
                    ->default(fn($livewire) => $livewire->getParentRecord()?->id)
                    ->required(),
                TextInput::make('slug')
                    ->label(__('admin/partnerCategory.fields.slug'))
                    ->placeholder(__('admin/partnerCategory.placeholders.slug'))
                    ->disabled()
                    ->columnSpanFull(),
                TextInput::make('video_url')
                    ->label(__('admin/partnerCategory.fields.video_url'))
                    ->placeholder(__('admin/partnerCategory.placeholders.video_url'))
                    ->columnSpanFull()
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
                    ->visibility('public')
                    ->columnSpanFull(),
                RichEditor::make('description')
                    ->label(__('admin/partnerCategory.fields.description'))
                    ->columnSpanFull(),
            ]);
    }
}
