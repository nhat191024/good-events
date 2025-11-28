<?php

namespace App\Filament\Admin\Pages;

use App\Models\Banner;

use UnitEnum;
use BackedEnum;

use App\Enum\NavigationGroup;
use App\Enum\BannerType;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

use Filament\Notifications\Notification;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class BannerManager extends Page implements HasForms
{
    use InteractsWithForms, HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected string $view = 'filament.admin.pages.banner-manager';

    public ?array $data = [];

    public Banner $partnerBanner;
    public Banner $partnerMobileBanner;
    public Banner $designBanner;
    public Banner $designMobileBanner;
    public Banner $rentalBanner;
    public Banner $rentalMobileBanner;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::SETTINGS->value;
    }

    public static function getNavigationLabel(): string
    {
        return __('admin/setting.banner');
    }

    public function getTitle(): string
    {
        return __('admin/setting.banner_settings');
    }

    public function mount(): void
    {
        $types = [
            BannerType::PARTNER->value,
            BannerType::DESIGN->value,
            BannerType::RENTAL->value,
            BannerType::PARTNER_MOBILE->value,
            BannerType::DESIGN_MOBILE->value,
            BannerType::RENTAL_MOBILE->value,
        ];

        $banners = Banner::with('media')->whereIn('type', $types)->get()->keyBy('type');

        $this->partnerBanner = $banners->get(BannerType::PARTNER->value) ?? tap(Banner::create(['type' => BannerType::PARTNER->value]), fn($b) => $b->setRelation('media', $b->newCollection()));
        $this->partnerMobileBanner = $banners->get(BannerType::PARTNER_MOBILE->value) ?? tap(Banner::create(['type' => BannerType::PARTNER_MOBILE->value]), fn($b) => $b->setRelation('media', $b->newCollection()));

        $this->designBanner = $banners->get(BannerType::DESIGN->value) ?? tap(Banner::create(['type' => BannerType::DESIGN->value]), fn($b) => $b->setRelation('media', $b->newCollection()));
        $this->designMobileBanner = $banners->get(BannerType::DESIGN_MOBILE->value) ?? tap(Banner::create(['type' => BannerType::DESIGN_MOBILE->value]), fn($b) => $b->setRelation('media', $b->newCollection()));

        $this->rentalBanner = $banners->get(BannerType::RENTAL->value) ?? tap(Banner::create(['type' => BannerType::RENTAL->value]), fn($b) => $b->setRelation('media', $b->newCollection()));
        $this->rentalMobileBanner = $banners->get(BannerType::RENTAL_MOBILE->value) ?? tap(Banner::create(['type' => BannerType::RENTAL_MOBILE->value]), fn($b) => $b->setRelation('media', $b->newCollection()));

        $this->form->fill([
            'partner' => $this->partnerBanner->getMedia('banners'),
            'mobile_partner' => $this->partnerMobileBanner->getMedia('banners'),
            'design' => $this->designBanner->getMedia('banners'),
            'mobile_design' => $this->designMobileBanner->getMedia('banners'),
            'rental' => $this->rentalBanner->getMedia('banners'),
            'mobile_rental' => $this->rentalMobileBanner->getMedia('banners'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Banners')
                    ->tabs([
                        //partner banners
                        Tab::make(__('admin/setting.banners.partner'))
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('partner')
                                    ->label(__('admin/setting.fields.banners.partner'))
                                    ->image()
                                    ->multiple()
                                    ->maxFiles(20)
                                    ->collection('banners')
                                    ->model($this->partnerBanner)
                                    ->reorderable()
                                    ->visibility('public')
                                    ->columnSpanFull()
                                    ->helperText(__('admin/setting.fields.banners.helper_text')),
                            ]),

                        Tab::make(__('admin/setting.banners.mobile_partner'))
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('mobile_partner')
                                    ->label(__('admin/setting.fields.banners.mobile_partner'))
                                    ->image()
                                    ->multiple()
                                    ->maxFiles(20)
                                    ->collection('banners')
                                    ->model($this->partnerMobileBanner)
                                    ->reorderable()
                                    ->visibility('public')
                                    ->columnSpanFull()
                                    ->helperText(__('admin/setting.fields.banners.helper_text')),
                            ]),

                        //design banners
                        Tab::make(__('admin/setting.banners.design'))
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('design')
                                    ->label(__('admin/setting.fields.banners.design'))
                                    ->image()
                                    ->multiple()
                                    ->maxFiles(20)
                                    ->collection('banners')
                                    ->model($this->designBanner)
                                    ->reorderable()
                                    ->visibility('public')
                                    ->columnSpanFull()
                                    ->helperText(__('admin/setting.fields.banners.helper_text')),
                            ]),

                        Tab::make(__('admin/setting.banners.mobile_design'))
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('mobile_design')
                                    ->label(__('admin/setting.fields.banners.mobile_design'))
                                    ->image()
                                    ->multiple()
                                    ->maxFiles(20)
                                    ->collection('banners')
                                    ->model($this->designMobileBanner)
                                    ->reorderable()
                                    ->visibility('public')
                                    ->columnSpanFull()
                                    ->helperText(__('admin/setting.fields.banners.helper_text')),
                            ]),

                        //rental banners
                        Tab::make(__('admin/setting.banners.rental'))
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('rental')
                                    ->label(__('admin/setting.fields.banners.rental'))
                                    ->image()
                                    ->multiple()
                                    ->maxFiles(20)
                                    ->collection('banners')
                                    ->model($this->rentalBanner)
                                    ->reorderable()
                                    ->visibility('public')
                                    ->columnSpanFull()
                                    ->helperText(__('admin/setting.fields.banners.helper_text')),
                            ]),

                        Tab::make(__('admin/setting.banners.mobile_rental'))
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('mobile_rental')
                                    ->label(__('admin/setting.fields.banners.mobile_rental'))
                                    ->image()
                                    ->multiple()
                                    ->maxFiles(20)
                                    ->collection('banners')
                                    ->model($this->rentalMobileBanner)
                                    ->reorderable()
                                    ->visibility('public')
                                    ->columnSpanFull()
                                    ->helperText(__('admin/setting.fields.banners.helper_text')),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $this->form->getState();

        Notification::make()
            ->title(__('admin/setting.saved'))
            ->success()
            ->send();
    }
}
