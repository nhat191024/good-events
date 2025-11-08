<?php

namespace App\Filament\Admin\Resources\DesignCategories\Pages;

use App\Filament\Admin\Resources\DesignCategories\DesignCategoryResource;
use App\Filament\Admin\Resources\DesignCategories\Resources\DesignCategoryChildrens\DesignCategoryChildrenResource;

use Filament\Tables\Table;
use Filament\Actions\CreateAction;

use Filament\Resources\Pages\ManageRelatedRecords;

class ManageDesignCategoryChildren extends ManageRelatedRecords
{
    protected static string $resource = DesignCategoryResource::class;

    protected static string $relationship = 'children';

    protected static ?string $relatedResource = DesignCategoryChildrenResource::class;

    public function getTitle(): string
    {
        return __('admin/category.manage_children_categories', ['name' => $this->getRecord()->name]);
    }

    public function getBreadcrumbs(): array
    {
        return [
            DesignCategoryResource::getIndexUrl() => __('admin/category.singulars.design'),
            $this->getRecord()->name,
            __('admin/category.children_category'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with('media')->withTrashed());
    }
}
