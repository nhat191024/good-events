<?php

namespace App\Filament\Admin\Resources\RentalCategories\Pages;

use App\Filament\Admin\Resources\RentalCategories\RentalCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRentalCategories extends ListRecords
{
    protected static string $resource = RentalCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
