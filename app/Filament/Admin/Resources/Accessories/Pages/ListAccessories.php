<?php

namespace App\Filament\Admin\Resources\Accessories\Pages;

use App\Filament\Admin\Resources\Accessories\AccessoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccessories extends ListRecords
{
    protected static string $resource = AccessoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
