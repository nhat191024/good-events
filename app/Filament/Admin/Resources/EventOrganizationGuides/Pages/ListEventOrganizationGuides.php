<?php

namespace App\Filament\Admin\Resources\EventOrganizationGuides\Pages;

use App\Filament\Admin\Resources\EventOrganizationGuides\EventOrganizationGuideResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventOrganizationGuides extends ListRecords
{
    protected static string $resource = EventOrganizationGuideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
