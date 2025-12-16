<?php

namespace App\Filament\Admin\Resources\DesignCategories\Resources\DesignCategoryChildrens\Pages;

use App\Filament\Admin\Resources\DesignCategories\Resources\DesignCategoryChildrens\DesignCategoryChildrenResource;
use App\Filament\Admin\Resources\DesignCategories\DesignCategoryResource;
use App\Filament\Admin\Resources\DesignCategories\Pages\ManageDesignCategoryChildren;
use Filament\Resources\Pages\CreateRecord;

class CreateDesignCategoryChildren extends CreateRecord
{
    protected static string $resource = DesignCategoryChildrenResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            DesignCategoryResource::getIndexUrl() => __('admin/category.singulars.design'),
            $this->getParentRecord()->name,
            ManageDesignCategoryChildren::getUrl([$this->getParentRecord()->id]) => __('admin/category.children_category'),
            __('global.create'),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'design';

        return $data;
    }
}
