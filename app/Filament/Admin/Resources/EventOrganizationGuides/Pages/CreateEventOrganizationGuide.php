<?php

namespace App\Filament\Admin\Resources\EventOrganizationGuides\Pages;

use App\Filament\Admin\Resources\EventOrganizationGuides\EventOrganizationGuideResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEventOrganizationGuide extends CreateRecord
{
    protected static string $resource = EventOrganizationGuideResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['type'] = 'event_organization_guide';
        return $data;
    }
}
