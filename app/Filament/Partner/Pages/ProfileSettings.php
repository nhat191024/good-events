<?php

namespace App\Filament\Partner\Pages;

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
use Illuminate\Support\Facades\Storage;

use RalphJSmit\Filament\Upload\Filament\Forms\Components\AdvancedFileUpload;

use Cohensive\OEmbed\Facades\OEmbed;

class ProfileSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected ?Location $cachedLocation = null;

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

    public function mount(): void
    {
        $user = Auth::user();
        $partnerProfile = $user->partnerProfile;

        $cityId = null;
        if ($partnerProfile?->location_id) {
            $this->cachedLocation = Location::find($partnerProfile->location_id);
            $cityId = $this->cachedLocation?->parent_id;
        }

        $this->data = [
            'name' => $user->name,
            'email' => $user->email,
            'country_code' => $user->country_code,
            'phone' => $user->phone,
            'partner_name' => $partnerProfile?->partner_name,
            'location_id' => $partnerProfile?->location_id,
            'city_id' => $cityId,
            'identity_card_number' => $partnerProfile?->identity_card_number,
            'selfie_image' => $partnerProfile?->selfie_image,
            'front_identity_card_image' => $partnerProfile?->front_identity_card_image,
            'back_identity_card_image' => $partnerProfile?->back_identity_card_image,
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

                            ->directory('uploads/avatars')
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
                                        Location::find($value)?->name
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
                                            : Location::find($value)?->name
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
                                    ->directory('uploads/partner_profiles/' . Auth::id() . '/selfies')
                                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
                                    ->disk('public')
                                    ->visibility('public')
                                    ->columnSpanFull()
                                    ->nullable(),

                                FileUpload::make('front_identity_card_image')
                                    ->label(__('profile.partner_label.front_identity_card_image'))
                                    ->image()
                                    ->imageEditor()
                                    ->directory('uploads/partner_profiles/' . Auth::id() . '/id_cards')
                                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
                                    ->disk('public')
                                    ->visibility('public')
                                    ->nullable(),

                                FileUpload::make('back_identity_card_image')
                                    ->label(__('profile.partner_label.back_identity_card_image'))
                                    ->image()
                                    ->imageEditor()
                                    ->directory('uploads/partner_profiles/' . Auth::id() . '/id_cards')
                                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
                                    ->disk('public')
                                    ->visibility('public')
                                    ->nullable(),
                            ])
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
            ];

            $partnerData = [
                'partner_name' => $data['partner_name'],
                'location_id' => $data['location_id'],
                'identity_card_number' => $data['identity_card_number'],
                'selfie_image' => $data['selfie_image'] ?? null,
                'front_identity_card_image' => $data['front_identity_card_image'] ?? null,
                'back_identity_card_image' => $data['back_identity_card_image'] ?? null,
                'video_url' => $data['video_url'] ?? null,
            ];

            $user->update($userData);

            $avatarState = $data['avatar'] ?? null;
            if ($avatarState) {
                $currentMedia = $user->getFirstMedia('avatar');
                $currentPath = $currentMedia ? ($currentMedia->id . '/' . $currentMedia->file_name) : null;

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

            Notification::make()
                ->title(__('profile.notifications.update_success_title'))
                ->success()
                ->send();
        } catch (\Exception $e) {


            Notification::make()
                ->title(__('profile.notifications.update_error_title'))
                ->body(__('profile.notifications.update_error_body'))
                ->danger()
                ->send();
        }
    }
}
