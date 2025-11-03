<?php

namespace App\Filament\Admin\Resources\ServiceRegistrations\Pages;

use App\Filament\Admin\Resources\ServiceRegistrations\ServiceRegistrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceRegistrations extends ListRecords
{
    protected static string $resource = ServiceRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
