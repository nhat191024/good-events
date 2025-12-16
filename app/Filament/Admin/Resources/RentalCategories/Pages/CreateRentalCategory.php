<?php

namespace App\Filament\Admin\Resources\RentalCategories\Pages;

use App\Filament\Admin\Resources\RentalCategories\RentalCategoryResource;
use App\Enum\CategoryType;

use Filament\Resources\Pages\CreateRecord;

class CreateRentalCategory extends CreateRecord
{
    protected static string $resource = RentalCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = CategoryType::RENTAL->value;

        return $data;
    }
}
