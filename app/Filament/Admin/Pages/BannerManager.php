<?php

namespace App\Filament\Admin\Pages;

use App\Models\Banner;

use UnitEnum;
use BackedEnum;

use App\Enum\FilamentNavigationGroup;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

use Filament\Notifications\Notification;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class BannerManager extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;
    protected static string|UnitEnum|null $navigationGroup = FilamentNavigationGroup::SETTINGS;

    protected string $view = 'filament.admin.pages.banner-manager';

    public ?array $data = [];

    public Banner $partnerBanner;
    public Banner $designBanner;
    public Banner $rentalBanner;

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
        $this->partnerBanner = Banner::firstOrCreate(['type' => 'partner']);
        $this->designBanner = Banner::firstOrCreate(['type' => 'design']);
        $this->rentalBanner = Banner::firstOrCreate(['type' => 'rental']);

        $this->form->fill([
            'banners' => $this->partnerBanner->getMedia('banners'),
            'design' => $this->designBanner->getMedia('banners'),
            'rental' => $this->rentalBanner->getMedia('banners'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Banners')
                    ->tabs([
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
