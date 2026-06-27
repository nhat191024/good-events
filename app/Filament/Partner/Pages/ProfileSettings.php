<?php

namespace App\Filament\Partner\Pages;

use App\Enum\CacheKey;
use App\Models\PartnerProfile;
use App\Models\Location;

use BackedEnum;

use Filament\Pages\Page;

use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;

use Filament\Actions\Action;

use Filament\Notifications\Notification;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

use RalphJSmit\Filament\Upload\Filament\Forms\Components\AdvancedFileUpload;

use Cohensive\OEmbed\Facades\OEmbed;

use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProfileSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected ?Location $cachedLocation = null;

    /**
     * @var array<int, string>
     */
    protected array $locationNameCache = [];

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-user-circle';
    protected string $view = 'filament.partner.pages.profile-settings';
    protected static ?int $navigationSort = 7;

    public static function getNavigationLabel(): string
    {
        return __('profile.profile');
    }

    public function getTitle(): string
    {
        return __('profile.profile');
    }

    public ?array $data = [];

    /**
     * @var list<int|string>
     */
    public array $selectedServiceAreaTableIds = [];

    public int $serviceAreaPage = 1;

    public int $serviceAreaPerPage = 10;

    public function mount(): void
    {
        $user = Auth::user();
        $user->loadMissing('partnerServiceAreas');
        $partnerProfile = $user->partnerProfile;

        $cityId = null;
        if ($partnerProfile?->location_id) {
            $this->cachedLocation = Location::find($partnerProfile->location_id);
            $cityId = $this->cachedLocation?->parent_id;
        }

        $avatar = $this->getMediaPath($user->getFirstMedia('avatar'));


        $this->data = [
            'name' => $user->name,
            'email' => $user->email,
            'country_code' => $user->country_code,
            'phone' => $user->phone,
            'bio' => $user->bio,
            'avatar' => $avatar,
            'partner_name' => $partnerProfile?->partner_name,
            'location_id' => $partnerProfile?->location_id,
            'service_area_location_ids' => $user->partnerServiceAreas->pluck('location_id')->values()->all(),
            'service_area_location_ids_to_add' => [],
            'service_area_city_id' => null,
            'city_id' => $cityId,
            'identity_card_number' => $partnerProfile?->identity_card_number,
            'selfie_image' => $this->withoutStoragePrefix($partnerProfile?->selfie_image),
            'front_identity_card_image' => $this->withoutStoragePrefix($partnerProfile?->front_identity_card_image),
            'back_identity_card_image' => $this->withoutStoragePrefix($partnerProfile?->back_identity_card_image),
            'video_url' => $partnerProfile?->video_url,
        ];

        $this->form->fill($this->data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('profile.user_info'))
                    ->description(__('profile.user_info_description'))
                    ->schema([
                        AdvancedFileUpload::make('avatar')
                            ->label(__('profile.label.avatar'))

                            ->temporaryFileUploadDisk('local')
                            ->disk('public')

                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
                            ->nullable(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('profile.label.name'))
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label(__('profile.label.email'))
                                    ->email()
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('country_code')
                                    ->label(__('profile.label.country_code'))
                                    ->placeholder('+84')
                                    ->maxLength(5),

                                TextInput::make('phone')
                                    ->label(__('profile.label.phone'))
                                    ->tel()
                                    ->maxLength(20),

                                RichEditor::make('bio')
                                    ->label(__('profile.label.bio'))
                                    ->columnSpanFull()
                                    ->nullable(),
                            ])
                    ]),

                Section::make(__('profile.partner_info'))
                    ->description(__('profile.partner_info_description'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('partner_name')
                                    ->label(__('profile.partner_label.partner_name'))
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('identity_card_number')
                                    ->label(__('profile.partner_label.identity_card_number'))
                                    ->required()
                                    ->maxLength(20),

                                Select::make('city_id')
                                    ->label(__('profile.partner_label.city_id'))
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
                                        $this->getLocationName($value)
                                    )
                                    ->afterStateUpdated(fn(callable $set) => $set('location_id', null))
                                    ->dehydrated(false)
                                    ->required(),

                                Select::make('location_id')
                                    ->label(__('profile.partner_label.location_id'))
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
                                        fn($value): ?string => ($this->cachedLocation?->id == $value)
                                            ? $this->cachedLocation->name
                                            : $this->getLocationName($value)
                                    )
                                    ->disabled(fn(Get $get): bool => !$get('city_id'))
                                    ->required(),

                                TextInput::make('video_url')
                                    ->label(__('profile.partner_label.video_url'))
                                    ->placeholder(__('profile.partner_placeholder.video_url'))
                                    ->helperText(__('profile.partner_helpers.video_url'))
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

                                FileUpload::make('selfie_image')
                                    ->label(__('profile.partner_label.selfie_image'))
                                    ->image()
                                    ->imageEditor()
                                    ->directory('uploads/partner/' . Auth::id())
                                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
                                    ->disk('local')
                                    ->visibility('private')
                                    ->columnSpanFull()
                                    ->nullable(),

                                FileUpload::make('front_identity_card_image')
                                    ->label(__('profile.partner_label.front_identity_card_image'))
                                    ->image()
                                    ->imageEditor()
                                    ->directory('uploads/partner/' . Auth::id())
                                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
                                    ->disk('local')
                                    ->visibility('private')
                                    ->nullable(),

                                FileUpload::make('back_identity_card_image')
                                    ->label(__('profile.partner_label.back_identity_card_image'))
                                    ->image()
                                    ->imageEditor()
                                    ->directory('uploads/partner/' . Auth::id())
                                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
                                    ->disk('local')
                                    ->visibility('private')
                                    ->nullable(),
                            ])
                    ]),

                Section::make('Vùng nhận đơn')
                    ->description('Chọn các phường/xã mà bạn muốn nhận đơn. Nếu chưa chọn vùng nào, hệ thống sẽ đẩy toàn bộ đơn đúng dịch vụ cho bạn.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('service_area_city_id')
                                    ->label('Thành phố / tỉnh')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->options(
                                        Location::query()
                                            ->whereNull('parent_id')
                                            ->orderBy('name')
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
                                        $this->getLocationName($value)
                                    )
                                    ->dehydrated(false),

                                Select::make('service_area_location_ids_to_add')
                                    ->label('Thêm phường/xã')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->options(fn(Get $get): array => $this->getServiceAreaOptions(
                                        $get('service_area_city_id'),
                                        $get('service_area_location_ids') ?? [],
                                    ))
                                    ->getSearchResultsUsing(fn(string $search, Get $get): array => $this->searchServiceAreaOptions(
                                        $search,
                                        $get('service_area_city_id'),
                                        $get('service_area_location_ids') ?? [],
                                    ))
                                    ->afterStateUpdated(function (Set $set, Get $get, ?array $state): void {
                                        $state = $state ?? [];

                                        $cityId = $get('service_area_city_id');
                                        $locationIdsToAdd = collect($state);

                                        if ($locationIdsToAdd->contains($this->getAddAllServiceAreasOptionKey())) {
                                            $locationIdsToAdd = $locationIdsToAdd
                                                ->reject(fn($locationId): bool => $locationId === $this->getAddAllServiceAreasOptionKey())
                                                ->merge($cityId ? $this->getWardIdsForCity((int) $cityId) : []);
                                        }

                                        $selectedLocationIds = collect($get('service_area_location_ids') ?? [])
                                            ->merge($locationIdsToAdd)
                                            ->map(fn($locationId): int => (int) $locationId)
                                            ->filter()
                                            ->unique()
                                            ->values()
                                            ->all();

                                        $set('service_area_location_ids', $selectedLocationIds);
                                        $set('service_area_location_ids_to_add', []);

                                        $this->data['service_area_location_ids'] = $selectedLocationIds;
                                        $this->data['service_area_location_ids_to_add'] = [];
                                        $this->serviceAreaPage = 1;
                                        $this->selectedServiceAreaTableIds = [];
                                    })
                                    ->helperText('Các vùng đã thêm sẽ hiển thị trong bảng bên dưới.')
                                    ->columnSpanFull(),
                            ]),
                    ])
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(__('profile.buttons.save_changes'))
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $user = Auth::user();

        $data = $this->form->getState();

        try {
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'country_code' => $data['country_code'] ?? null,
                'phone' => $data['phone'] ?? null,
                'bio' => $data['bio']
            ];

            $partnerData = [
                'partner_name' => $data['partner_name'],
                'location_id' => $data['location_id'],
                'identity_card_number' => $data['identity_card_number'],
                'video_url' => $data['video_url'] ?? null,
            ];

            foreach (['selfie_image', 'front_identity_card_image', 'back_identity_card_image'] as $imageField) {
                if (!empty($data[$imageField])) {
                    $partnerData[$imageField] = $this->withoutStoragePrefix($data[$imageField]);
                }
            }

            $user->update($userData);

            $avatarState = $data['avatar'] ?? null;
            if ($avatarState) {
                $currentMedia = $user->getFirstMedia('avatar');
                $currentPath = $this->getMediaPath($currentMedia);

                if ($avatarState !== $currentPath && !str_starts_with($avatarState, 'http')) {
                    $validPath = null;
                    if (Storage::disk('public')->exists($avatarState)) {
                        $validPath = $avatarState;
                    } elseif (Storage::disk('public')->exists('uploads/avatars/' . $avatarState)) {
                        $validPath = 'uploads/avatars/' . $avatarState;
                    }

                    if ($validPath) {
                        $user->clearMediaCollection('avatar');
                        $user->addMediaFromDisk($validPath, 'public')->toMediaCollection('avatar');
                    }
                }
            } else {
                $user->clearMediaCollection('avatar');
            }

            $partnerProfile = $user->partnerProfile;
            if ($partnerProfile) {
                $partnerProfile->update($partnerData);
            } else {
                PartnerProfile::create([
                    'user_id' => $user->id,
                    ...$partnerData
                ]);
            }

            $this->syncPartnerServiceAreas($this->data['service_area_location_ids'] ?? []);

            $newMedia = $user->refresh()->getFirstMedia('avatar');
            $this->data['avatar'] = $this->getMediaPath($newMedia);

            Notification::make()
                ->title(__('profile.notifications.update_success_title'))
                ->success()
                ->send();

            $this->dispatch('refresh-topbar');
        } catch (\Exception $e) {
            Log::error('Profile update error for user ID ' . $user->id . ': ' . $e->getMessage());

            Notification::make()
                ->title(__('profile.notifications.update_error_title'))
                ->body(__('profile.notifications.update_error_body'))
                ->danger()
                ->send();
        }
    }

    public function removeServiceArea(int $locationId): void
    {
        $this->data['service_area_location_ids'] = collect($this->data['service_area_location_ids'] ?? [])
            ->map(fn($id): int => (int) $id)
            ->reject(fn(int $id): bool => $id === $locationId)
            ->values()
            ->all();

        $this->selectedServiceAreaTableIds = collect($this->selectedServiceAreaTableIds)
            ->map(fn($id): int => (int) $id)
            ->reject(fn(int $id): bool => $id === $locationId)
            ->values()
            ->all();

        $this->normalizeServiceAreaPage();
    }

    public function removeSelectedServiceAreas(): void
    {
        $locationIdsToRemove = collect($this->selectedServiceAreaTableIds)
            ->map(fn($id): int => (int) $id)
            ->filter()
            ->unique();

        if ($locationIdsToRemove->isEmpty()) {
            return;
        }

        $this->data['service_area_location_ids'] = collect($this->data['service_area_location_ids'] ?? [])
            ->map(fn($id): int => (int) $id)
            ->reject(fn(int $id): bool => $locationIdsToRemove->contains($id))
            ->values()
            ->all();

        $this->selectedServiceAreaTableIds = [];
        $this->normalizeServiceAreaPage();
    }

    /**
     * @return array{data: list<array{id: int, name: string, province_name: string|null}>, total: int, current_page: int, per_page: int, last_page: int, from: int|null, to: int|null}
     */
    public function getPaginatedServiceAreaRows(): array
    {
        $locationIds = $this->normalizeServiceAreaLocationIds($this->data['service_area_location_ids'] ?? []);
        $total = count($locationIds);
        $lastPage = max(1, (int) ceil($total / $this->serviceAreaPerPage));
        $currentPage = min(max(1, $this->serviceAreaPage), $lastPage);
        $offset = ($currentPage - 1) * $this->serviceAreaPerPage;

        if ($total === 0) {
            return [
                'data' => [],
                'total' => 0,
                'current_page' => 1,
                'per_page' => $this->serviceAreaPerPage,
                'last_page' => 1,
                'from' => null,
                'to' => null,
            ];
        }

        $pageLocationIds = array_slice($locationIds, $offset, $this->serviceAreaPerPage);

        $rows = Location::query()
            ->with('province:id,name')
            ->whereIn('id', $pageLocationIds)
            ->orderBy('name')
            ->get()
            ->map(fn(Location $location): array => [
                'id' => $location->id,
                'name' => $location->name,
                'province_name' => $location->province?->name,
            ])
            ->values()
            ->all();

        return [
            'data' => $rows,
            'total' => $total,
            'current_page' => $currentPage,
            'per_page' => $this->serviceAreaPerPage,
            'last_page' => $lastPage,
            'from' => $offset + 1,
            'to' => min($offset + $this->serviceAreaPerPage, $total),
        ];
    }

    public function previousServiceAreaPage(): void
    {
        $this->serviceAreaPage = max(1, $this->serviceAreaPage - 1);
        $this->selectedServiceAreaTableIds = [];
    }

    public function nextServiceAreaPage(): void
    {
        $this->serviceAreaPage = min($this->getServiceAreaLastPage(), $this->serviceAreaPage + 1);
        $this->selectedServiceAreaTableIds = [];
    }

    private function normalizeServiceAreaPage(): void
    {
        $this->serviceAreaPage = min($this->serviceAreaPage, $this->getServiceAreaLastPage());
        $this->serviceAreaPage = max(1, $this->serviceAreaPage);
    }

    private function getServiceAreaLastPage(): int
    {
        $total = count($this->normalizeServiceAreaLocationIds($this->data['service_area_location_ids'] ?? []));

        return max(1, (int) ceil($total / $this->serviceAreaPerPage));
    }

    private function getMediaPath(?Media $media): ?string
    {
        return $media ? "uploads/{$media->id}/{$media->file_name}" : null;
    }

    private function withoutStoragePrefix(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $path = ltrim($path, '/');

        return str_starts_with($path, 'storage/') ? substr($path, strlen('storage/')) : $path;
    }

    /**
     * @param array<int, int|string> $selectedLocationIds
     */
    private function getServiceAreaOptions(null|int|string $cityId, array $selectedLocationIds = []): array
    {
        $selectedLocationIds = $this->normalizeServiceAreaLocationIds($selectedLocationIds);

        if (!$cityId) {
            return [];
        }

        $wardOptions = Location::query()
            ->with('province:id,name')
            ->where('parent_id', $cityId)
            ->whereNotIn('id', $selectedLocationIds)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn(Location $location): array => [
                $location->id => $location->name,
            ])
            ->toArray();

        if (empty($wardOptions)) {
            return [];
        }

        return [
            $this->getAddAllServiceAreasOptionKey() => 'Thêm toàn bộ phường/xã của thành phố này',
        ] + $wardOptions;
    }

    /**
     * @param array<int, int|string> $selectedLocationIds
     */
    private function searchServiceAreaOptions(string $search, null|int|string $cityId, array $selectedLocationIds = []): array
    {
        if (!$cityId) {
            return [];
        }

        $selectedLocationIds = $this->normalizeServiceAreaLocationIds($selectedLocationIds);

        return Location::query()
            ->with('province:id,name')
            ->where('parent_id', $cityId)
            ->whereNotIn('id', $selectedLocationIds)
            ->where('name', 'like', "%{$search}%")
            ->limit(50)
            ->get()
            ->mapWithKeys(fn(Location $location): array => [
                $location->id => $location->name,
            ])
            ->toArray();
    }

    private function getLocationName(null|int|string $locationId): ?string
    {
        if (!$locationId) {
            return null;
        }

        $locationId = (int) $locationId;

        if (isset($this->locationNameCache[$locationId])) {
            return $this->locationNameCache[$locationId];
        }

        $locationName = Location::query()
            ->whereKey($locationId)
            ->value('name');

        return $locationName
            ? $this->locationNameCache[$locationId] = $locationName
            : null;
    }

    private function getAddAllServiceAreasOptionKey(): string
    {
        return '__all_city_service_areas__';
    }

    /**
     * @return list<int>
     */
    private function getWardIdsForCity(int $cityId): array
    {
        return Location::query()
            ->where('parent_id', $cityId)
            ->whereNotNull('parent_id')
            ->pluck('id')
            ->map(fn($locationId): int => (int) $locationId)
            ->all();
    }

    /**
     * @param array<int, int|string> $locationIds
     * @return list<int>
     */
    private function normalizeServiceAreaLocationIds(array $locationIds): array
    {
        return collect($locationIds)
            ->reject(fn($locationId): bool => $locationId === $this->getAddAllServiceAreasOptionKey())
            ->map(fn($locationId): int => (int) $locationId)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param array<int, int|string> $locationIds
     */
    private function syncPartnerServiceAreas(array $locationIds): void
    {
        $user = Auth::user();

        $validLocationIds = Location::query()
            ->whereIn('id', collect($locationIds)->map(fn($id): int => (int) $id)->unique()->values())
            ->whereNotNull('parent_id')
            ->pluck('id')
            ->all();

        if (empty($validLocationIds)) {
            $user->partnerServiceAreas()->delete();
            Cache::tags([CacheKey::PARTNER_SERVICE_AREAS->value])->flush();

            return;
        }

        $user->partnerServiceAreas()
            ->whereNotIn('location_id', $validLocationIds)
            ->delete();

        foreach ($validLocationIds as $locationId) {
            $user->partnerServiceAreas()->firstOrCreate([
                'location_id' => $locationId,
            ]);
        }

        Cache::tags([CacheKey::PARTNER_SERVICE_AREAS->value])->flush();
    }
}
