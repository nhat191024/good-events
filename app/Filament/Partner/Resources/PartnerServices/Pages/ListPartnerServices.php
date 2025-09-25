<?php

namespace App\Filament\Partner\Resources\PartnerServices\Pages;

use App\Filament\Partner\Resources\PartnerServices\PartnerServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPartnerServices extends ListRecords
{
    protected static string $resource = PartnerServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('partner/service.button.add_service')),
        ];
    }
}
