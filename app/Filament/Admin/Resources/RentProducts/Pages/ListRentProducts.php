<?php

namespace App\Filament\Admin\Resources\RentProducts\Pages;

use App\Filament\Admin\Resources\RentProducts\RentProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRentProducts extends ListRecords
{
    protected static string $resource = RentProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
