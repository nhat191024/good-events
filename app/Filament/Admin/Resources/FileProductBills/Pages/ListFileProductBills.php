<?php

namespace App\Filament\Admin\Resources\FileProductBills\Pages;

use App\Filament\Admin\Resources\FileProductBills\FileProductBillResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFileProductBills extends ListRecords
{
    protected static string $resource = FileProductBillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
