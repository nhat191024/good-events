<?php

namespace App\Filament\Admin\Resources\Categories\Pages;

use App\Filament\Admin\Resources\Categories\CategoryResource;
use App\Filament\Admin\Resources\Categories\Resources\ChildrenCategories\ChildrenCategoryResource;

use Filament\Tables\Table;
use Filament\Actions\CreateAction;

use Filament\Resources\Pages\ManageRelatedRecords;

class ManageChildrenCategory extends ManageRelatedRecords
{
    protected static string $resource = CategoryResource::class;

    protected static string $relationship = 'children';

    protected static ?string $relatedResource = ChildrenCategoryResource::class;

    public function getTitle(): string
    {
        return __('admin/category.manage_children_categories', ['name' => $this->getRecord()->name]);
    }

    public function getBreadcrumbs(): array
    {
        return [
            CategoryResource::getIndexUrl() => __('admin/category.plural'),
            $this->getRecord()->name,
            __('global.list'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with('media')->withTrashed())
            ->headerActions([
                CreateAction::make()
                    ->label(__('admin/category.actions.create_child_category')),
            ]);
    }
}
