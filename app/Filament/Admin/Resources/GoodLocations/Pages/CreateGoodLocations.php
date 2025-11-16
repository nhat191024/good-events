<?php

namespace App\Filament\Admin\Resources\GoodLocations\Pages;

use App\Filament\Admin\Resources\GoodLocations\GoodLocationsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGoodLocations extends CreateRecord
{
    protected static string $resource = GoodLocationsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['type'] = 'good_location';
        return $data;
    }
}
