<?php

namespace App\Filament\Admin\Resources\Accessories\Pages;

use App\Filament\Admin\Resources\Accessories\AccessoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAccessory extends EditRecord
{
    protected static string $resource = AccessoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
