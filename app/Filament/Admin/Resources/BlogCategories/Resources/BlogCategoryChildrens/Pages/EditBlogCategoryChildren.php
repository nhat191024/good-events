<?php

namespace App\Filament\Admin\Resources\BlogCategories\Resources\BlogCategoryChildrens\Pages;

use App\Filament\Admin\Resources\BlogCategories\Resources\BlogCategoryChildrens\BlogCategoryChildrenResource;
use App\Filament\Admin\Resources\BlogCategories\BlogCategoryResource;
use App\Filament\Admin\Resources\BlogCategories\Pages\ManageBlogCategoryChildren;
use Filament\Resources\Pages\EditRecord;

use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;

class EditBlogCategoryChildren extends EditRecord
{
    protected static string $resource = BlogCategoryChildrenResource::class;

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
            BlogCategoryResource::getIndexUrl() => __('admin/category.singulars.blog'),
            $this->getParentRecord()->name,
            ManageBlogCategoryChildren::getUrl([$this->getParentRecord()->id]) => __('admin/category.children_category'),
            __('global.create'),
        ];
    }
}
