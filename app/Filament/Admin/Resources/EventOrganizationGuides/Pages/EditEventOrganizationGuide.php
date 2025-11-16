<?php

namespace App\Filament\Admin\Resources\EventOrganizationGuides\Pages;

use App\Filament\Admin\Resources\EventOrganizationGuides\EventOrganizationGuideResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditEventOrganizationGuide extends EditRecord
{
    protected static string $resource = EventOrganizationGuideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('global.hidden')),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
