<?php

namespace App\Filament\Admin\Resources\RentProducts\Pages;

use App\Filament\Admin\Resources\RentProducts\RentProductResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRentProduct extends EditRecord
{
    protected static string $resource = RentProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            // ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
