<?php

namespace App\Filament\Admin\Resources\BlogCategories\Resources\BlogCategoryChildrens\Pages;

use App\Filament\Admin\Resources\BlogCategories\Resources\BlogCategoryChildrens\BlogCategoryChildrenResource;
use App\Filament\Admin\Resources\BlogCategories\BlogCategoryResource;
use App\Filament\Admin\Resources\BlogCategories\Pages\ManageBlogCategoryChildren;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogCategoryChildren extends CreateRecord
{
    protected static string $resource = BlogCategoryChildrenResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            BlogCategoryResource::getIndexUrl() => __('admin/category.singulars.blog'),
            $this->getParentRecord()->name,
            ManageBlogCategoryChildren::getUrl([$this->getParentRecord()->id]) => __('admin/category.children_category'),
            __('global.create'),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'blog';

        return $data;
    }
}
