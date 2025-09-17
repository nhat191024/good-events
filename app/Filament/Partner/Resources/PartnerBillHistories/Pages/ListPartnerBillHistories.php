<?php

namespace App\Filament\Partner\Resources\PartnerBillHistories\Pages;

use App\Filament\Partner\Resources\PartnerBillHistories\PartnerBillHistoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPartnerBillHistories extends ListRecords
{
    protected static string $resource = PartnerBillHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
