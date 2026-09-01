<?php

namespace App\Filament\Admin\Resources\EventCategories\Resources\PartnerCategories\Pages;

use App\Filament\Admin\Resources\EventCategories\EventCategoryResource;
use App\Filament\Admin\Resources\EventCategories\Pages\ManagePartnerCategory;
use App\Filament\Admin\Resources\EventCategories\Resources\PartnerCategories\PartnerCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePartnerCategory extends CreateRecord
{
    protected static string $resource = PartnerCategoryResource::class;

    /** @var list<string> */
    private array $accessoryNames = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->accessoryNames = array_values($data['accessory_names'] ?? []);
        unset($data['accessory_names']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->accessories()->createMany(
            array_map(fn (string $name): array => ['name' => $name], $this->accessoryNames)
        );
    }

    public function getTitle(): string
    {
        return __('admin/partnerCategory.create_partner_category');
    }

    public function getBreadcrumbs(): array
    {
        return [
            EventCategoryResource::getIndexUrl() => __('admin/partnerCategory.plural'),
            ManagePartnerCategory::getUrl([$this->getParentRecord()->id]) => $this->getParentRecord()->name,
            __('global.create'),
        ];
    }
}
