<?php

namespace App\Filament\Admin\Resources\FileProductBills\Pages;

use App\Filament\Admin\Resources\FileProductBills\FileProductBillResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFileProductBill extends EditRecord
{
    protected static string $resource = FileProductBillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }
}
