<?php

namespace App\Filament\Admin\Resources\EventCategories\Resources\PartnerCategories\Pages;

use App\Filament\Admin\Resources\EventCategories\Resources\PartnerCategories\PartnerCategoryResource;
use App\Filament\Admin\Resources\EventCategories\EventCategoryResource;
use App\Filament\Admin\Resources\EventCategories\Pages\ManagePartnerCategory;
use Filament\Resources\Pages\CreateRecord;

class CreatePartnerCategory extends CreateRecord
{
    protected static string $resource = PartnerCategoryResource::class;

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
