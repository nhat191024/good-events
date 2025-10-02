<?php

namespace App\Filament\Admin\Resources\PartnerBills\Pages;

use App\Filament\Admin\Resources\PartnerBills\PartnerBillResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPartnerBills extends ListRecords
{
    protected static string $resource = PartnerBillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
