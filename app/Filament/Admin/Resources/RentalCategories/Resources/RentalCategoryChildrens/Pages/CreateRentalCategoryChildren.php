<?php

namespace App\Filament\Admin\Resources\RentalCategories\Resources\RentalCategoryChildrens\Pages;

use App\Filament\Admin\Resources\RentalCategories\Resources\RentalCategoryChildrens\RentalCategoryChildrenResource;
use App\Filament\Admin\Resources\RentalCategories\RentalCategoryResource;
use App\Filament\Admin\Resources\RentalCategories\Pages\ManageRentalCategoryChildren;
use Filament\Resources\Pages\CreateRecord;

class CreateRentalCategoryChildren extends CreateRecord
{
    protected static string $resource = RentalCategoryChildrenResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            RentalCategoryResource::getIndexUrl() => __('admin/category.singulars.rental'),
            $this->getParentRecord()->name,
            ManageRentalCategoryChildren::getUrl([$this->getParentRecord()->id]) => __('admin/category.children_category'),
            __('global.create'),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'rental';

        return $data;
    }
}
