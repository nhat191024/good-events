<?php

namespace App\Filament\Admin\Resources\GoodLocations\Pages;

use App\Filament\Admin\Resources\GoodLocations\GoodLocationsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditGoodLocations extends EditRecord
{
    protected static string $resource = GoodLocationsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('global.hidden')),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
