<?php

namespace App\Filament\Admin\Resources\DesignCategories\Resources\DesignCategoryChildrens\Pages;

use App\Filament\Admin\Resources\DesignCategories\Resources\DesignCategoryChildrens\DesignCategoryChildrenResource;
use App\Filament\Admin\Resources\DesignCategories\DesignCategoryResource;
use App\Filament\Admin\Resources\DesignCategories\Pages\ManageDesignCategoryChildren;
use Filament\Resources\Pages\EditRecord;

use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;

class EditDesignCategoryChildren extends EditRecord
{
    protected static string $resource = DesignCategoryChildrenResource::class;

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
            DesignCategoryResource::getIndexUrl() => __('admin/category.singulars.design'),
            $this->getParentRecord()->name,
            ManageDesignCategoryChildren::getUrl([$this->getParentRecord()->id]) => __('admin/category.children_category'),
            __('global.edit'),
        ];
    }
}
