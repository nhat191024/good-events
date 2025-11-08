<?php

namespace App\Filament\Admin\Resources\RentalCategories\Pages;

use App\Filament\Admin\Resources\RentalCategories\RentalCategoryResource;
use App\Filament\Admin\Resources\RentalCategories\Resources\RentalCategoryChildrens\RentalCategoryChildrenResource;

use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;

class ManageRentalCategoryChildren extends ManageRelatedRecords
{
    protected static string $resource = RentalCategoryResource::class;

    protected static string $relationship = 'children';

    protected static ?string $relatedResource = RentalCategoryChildrenResource::class;

    public function getTitle(): string
    {
        return __('admin/category.manage_children_categories', ['name' => $this->getRecord()->name]);
    }

    public function getBreadcrumbs(): array
    {
        return [
            RentalCategoryResource::getIndexUrl() => __('admin/category.singulars.design'),
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
