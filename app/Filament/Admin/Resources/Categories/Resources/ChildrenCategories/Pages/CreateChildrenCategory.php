<?php

namespace App\Filament\Admin\Resources\Categories\Resources\ChildrenCategories\Pages;

use App\Filament\Admin\Resources\Categories\Resources\ChildrenCategories\ChildrenCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChildrenCategory extends CreateRecord
{
    protected static string $resource = ChildrenCategoryResource::class;
}
