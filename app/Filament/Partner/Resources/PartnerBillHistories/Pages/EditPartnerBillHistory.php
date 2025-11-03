<?php

namespace App\Filament\Partner\Resources\PartnerBillHistories\Pages;

use App\Filament\Partner\Resources\PartnerBillHistories\PartnerBillHistoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPartnerBillHistory extends EditRecord
{
    protected static string $resource = PartnerBillHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
