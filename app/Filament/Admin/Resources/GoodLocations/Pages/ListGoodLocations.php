<?php

namespace App\Filament\Admin\Resources\GoodLocations\Pages;

use App\Filament\Admin\Resources\GoodLocations\GoodLocationsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGoodLocations extends ListRecords
{
    protected static string $resource = GoodLocationsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
