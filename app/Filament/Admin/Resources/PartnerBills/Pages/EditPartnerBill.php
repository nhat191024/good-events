<?php

namespace App\Filament\Admin\Resources\PartnerBills\Pages;

use App\Filament\Admin\Resources\PartnerBills\PartnerBillResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPartnerBill extends EditRecord
{
    protected static string $resource = PartnerBillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
