<?php

namespace App\Filament\Admin\Resources\GoodLocations\Schemas;

use App\Models\Category;
use App\Models\Location;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;

use Filament\Actions\Action;
use Filament\Notifications\Notification;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;

use OpenCage\Geocoder\Geocoder;
use Dotswan\MapPicker\Fields\Map;

use Cohensive\OEmbed\Facades\OEmbed;

class GoodLocationsForm
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
                                TextInput::make('max_people')
                                    ->label(__('admin/blog.fields.max_people'))
                                    ->placeholder(__('admin/blog.placeholders.max_people'))
                                    ->numeric()
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
                                if ($state) {
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
                                return null;
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

                Section::make(__('admin/blog.sections.location'))
                    ->description(__('admin/blog.sections.location_description'))
                    ->icon('heroicon-o-map-pin')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('city_id')
                                    ->label(__('admin/blog.fields.city'))
                                    ->placeholder(__('admin/blog.placeholders.city'))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->options(
                                        Location::query()
                                            ->whereNull('parent_id')
                                            ->pluck('name', 'id')
                                    )
                                    ->getSearchResultsUsing(
                                        fn(string $search): array =>
                                        Location::query()
                                            ->whereNull('parent_id')
                                            ->where('name', 'like', "%{$search}%")
                                            ->limit(50)
                                            ->pluck('name', 'id')
                                            ->toArray()
                                    )
                                    ->getOptionLabelUsing(
                                        fn($value): ?string =>
                                        Location::find($value)?->name
                                    )
                                    ->afterStateUpdated(fn(callable $set) => $set('location_id', null))
                                    ->afterStateHydrated(function ($state, $record, Set $set): void {
                                        if ($record && $record->location_id) {
                                            $ward = Location::find($record->location_id);
                                            if ($ward && $ward->parent_id) {
                                                $set('city_id', $ward->parent_id);
                                            }
                                        }
                                    })
                                    ->dehydrated(false)
                                    ->required(),
                                Select::make('location_id')
                                    ->label(__('admin/blog.fields.ward'))
                                    ->placeholder(__('admin/blog.placeholders.ward'))
                                    ->searchable()
                                    ->options(function (Get $get): array {
                                        $cityId = $get('city_id');
                                        if (!$cityId) {
                                            return [];
                                        }
                                        return Location::query()
                                            ->where('parent_id', $cityId)
                                            ->whereNotNull('parent_id')
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->getSearchResultsUsing(
                                        fn(string $search, Get $get): array =>
                                        Location::query()
                                            ->where('parent_id', $get('city_id'))
                                            ->whereNotNull('parent_id')
                                            ->where('name', 'like', "%{$search}%")
                                            ->limit(50)
                                            ->pluck('name', 'id')
                                            ->toArray()
                                    )
                                    ->getOptionLabelUsing(
                                        fn($value): ?string =>
                                        Location::find($value)?->name
                                    )
                                    ->disabled(fn(Get $get): bool => !$get('city_id'))
                                    ->required(),
                            ]),
                        TextInput::make('address')
                            ->label(__('admin/blog.fields.address'))
                            ->placeholder(__('admin/blog.placeholders.address'))
                            ->required(),
                    ])
                    ->columnSpanFull(),

                Section::make(__('admin/blog.sections.map'))
                    ->description(__('admin/blog.sections.map_description'))
                    ->icon('heroicon-o-globe-alt')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('latitude')
                                    ->label(__('admin/blog.fields.latitude'))
                                    ->helperText(__('admin/blog.helpers.latitude'))
                                    ->placeholder(__('admin/blog.placeholders.latitude'))
                                    ->required(),
                                TextInput::make('longitude')
                                    ->label(__('admin/blog.fields.longitude'))
                                    ->helperText(__('admin/blog.helpers.longitude'))
                                    ->placeholder(__('admin/blog.placeholders.longitude'))
                                    ->required(),
                                TextInput::make('search')
                                    ->label(__('admin/blog.fields.search'))
                                    ->placeholder(__('admin/blog.placeholders.search'))
                                    ->helperText(__('admin/blog.helpers.search'))
                                    ->suffixAction(
                                        Action::make('geocode')
                                            ->icon('heroicon-o-magnifying-glass')
                                            ->action(function (Set $set, Get $get, $state, $livewire) {
                                                if (empty($state)) {
                                                    Notification::make()
                                                        ->title('Vui lòng nhập địa chỉ')
                                                        ->warning()
                                                        ->send();
                                                    return;
                                                }

                                                try {
                                                    $apiKey = config('services.opencage.key');

                                                    if (empty($apiKey)) {
                                                        Notification::make()
                                                            ->title('Thiếu OpenCage API Key')
                                                            ->body('Vui lòng cấu hình OPEN_CAGE_API_KEY trong file .env')
                                                            ->danger()
                                                            ->send();
                                                        return;
                                                    }

                                                    $geocode = new Geocoder($apiKey);
                                                    $result = $geocode->geocode($state, ['limit' => 1]);

                                                    if ($result && $result['total_results'] > 0) {
                                                        $location = $result['results'][0]['geometry'];
                                                        $lat = $location['lat'];
                                                        $lng = $location['lng'];

                                                        $set('latitude', $lat);
                                                        $set('longitude', $lng);
                                                        $set('location', ['lat' => $lat, 'lng' => $lng]);

                                                        $livewire->dispatch('refreshMap');

                                                        Notification::make()
                                                            ->title('Tìm thấy tọa độ!')
                                                            ->body('Lat: ' . $lat . ', Lng: ' . $lng)
                                                            ->success()
                                                            ->send();
                                                    } else {
                                                        Notification::make()
                                                            ->title('Không tìm thấy địa chỉ')
                                                            ->body('Vui lòng thử địa chỉ khác hoặc chi tiết hơn')
                                                            ->warning()
                                                            ->send();
                                                    }
                                                } catch (\Exception $e) {
                                                    Notification::make()
                                                        ->title('Lỗi')
                                                        ->body($e->getMessage())
                                                        ->danger()
                                                        ->send();
                                                }
                                            })
                                    ),
                            ]),
                        Map::make('location')
                            ->label(__('admin/blog.fields.map'))
                            ->defaultLocation(latitude: 21.0285, longitude: 105.804)
                            ->draggable(true)
                            ->clickable(true)
                            ->zoom(15)
                            ->minZoom(0)
                            ->maxZoom(28)
                            ->tilesUrl("https://tile.openstreetmap.de/{z}/{x}/{y}.png")
                            ->detectRetina(true)
                            ->showMarker(true)
                            ->markerColor("#3b82f6")
                            ->markerIconUrl('/images/marker.png')
                            ->markerIconSize([26, 26])
                            ->markerIconAnchor([18, 36])
                            ->showFullscreenControl(true)
                            ->showZoomControl(true)
                            ->showMyLocationButton(false)
                            ->extraStyles([
                                'min-height: 40vh',
                                'border-radius: 25px'
                            ])
                            ->afterStateUpdated(function (Set $set, ?array $state): void {
                                if ($state && isset($state['lat'], $state['lng'])) {
                                    $set('latitude', $state['lat']);
                                    $set('longitude', $state['lng']);
                                }
                            })
                            ->afterStateHydrated(function ($state, $record, Set $set): void {
                                if ($record && $record->latitude && $record->longitude) {
                                    $set('location', ['lat' => $record->latitude, 'lng' => $record->longitude]);
                                }
                            }),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
