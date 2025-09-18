<?php

namespace App\Filament\Partner\Resources\PartnerServices\Pages;

use App\Filament\Partner\Resources\PartnerServices\PartnerServiceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPartnerService extends EditRecord
{
    protected static string $resource = PartnerServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
