<?php

namespace App\Filament\Admin\Resources\BlogCategories\Pages;

use App\Filament\Admin\Resources\BlogCategories\BlogCategoryResource;
use App\Filament\Admin\Resources\BlogCategories\Resources\BlogCategoryChildrens\BlogCategoryChildrenResource;

use Filament\Tables\Table;
use Filament\Actions\CreateAction;

use Filament\Resources\Pages\ManageRelatedRecords;

class ManageBlogCategoryChildren extends ManageRelatedRecords
{
    protected static string $resource = BlogCategoryResource::class;

    protected static string $relationship = 'children';

    protected static ?string $relatedResource = BlogCategoryChildrenResource::class;

    public function getTitle(): string
    {
        return __('admin/category.manage_children_categories', ['name' => $this->getRecord()->name]);
    }

    public function getBreadcrumbs(): array
    {
        return [
            BlogCategoryResource::getIndexUrl() => __('admin/category.singulars.blog'),
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
