<?php

namespace App\Filament\Admin\Resources\RentalCategories\Resources\RentalCategoryChildrens\Pages;

use App\Filament\Admin\Resources\RentalCategories\Resources\RentalCategoryChildrens\RentalCategoryChildrenResource;
use App\Filament\Admin\Resources\RentalCategories\RentalCategoryResource;
use App\Filament\Admin\Resources\RentalCategories\Pages\ManageRentalCategoryChildren;
use Filament\Resources\Pages\EditRecord;

use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;

class EditRentalCategoryChildren extends EditRecord
{
    protected static string $resource = RentalCategoryChildrenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('global.hidden')),
            RestoreAction::make(),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            RentalCategoryResource::getIndexUrl() => __('admin/category.singulars.rental'),
            $this->getParentRecord()->name,
            ManageRentalCategoryChildren::getUrl([$this->getParentRecord()->id]) => __('admin/category.children_category'),
            __('global.edit'),
        ];
    }
}
