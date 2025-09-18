<?php

namespace App\Filament\Partner\Pages;

use App\Models\PartnerProfile;
use App\Models\Location;

use BackedEnum;
use Faker\Core\File;
use Filament\Pages\Page;

use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;

use Filament\Actions\Action;

use Filament\Notifications\Notification;

use Illuminate\Support\Facades\Auth;

class ProfileSettings extends Page implements HasForms
{
    use InteractsWithForms;

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

        $this->data = [
            'avatar' => $user->avatar,
            'name' => $user->name,
            'email' => $user->email,
            'country_code' => $user->country_code,
            'phone' => $user->phone,
            'partner_name' => $partnerProfile?->partner_name,
            'identity_card_number' => $partnerProfile?->identity_card_number,
            'location_id' => $partnerProfile?->location_id,
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
                        FileUpload::make('avatar')
                            ->label(__('profile.label.avatar'))
                            ->avatar()
                            ->imageEditor()
                            ->directory('uploads/avatars')
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
                            ->disk('public')
                            ->visibility('public')
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

                                Select::make('location_id')
                                    ->label(__('profile.partner_label.location_id'))
                                    ->options(Location::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),
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

        // Get form data and validate
        $data = $this->form->getState();

        try {
            // Simple validation (Filament form validation is already applied)
            $userData = [
                // 'avatar' => "storage/{$data['avatar']}" ?? null,
                'avatar' => $data['avatar'] ?? null,
                'name' => $data['name'],
                'email' => $data['email'],
                'country_code' => $data['country_code'] ?? null,
                'phone' => $data['phone'] ?? null,
            ];

            $partnerData = [
                'partner_name' => $data['partner_name'],
                'identity_card_number' => $data['identity_card_number'],
                'location_id' => $data['location_id'],
            ];

            // Update user information
            $user->update($userData);

            // Update or create partner profile
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
                ->title('Profile updated successfully!')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error updating profile')
                ->body('Please try again later.')
                ->danger()
                ->send();
        }
    }
}
