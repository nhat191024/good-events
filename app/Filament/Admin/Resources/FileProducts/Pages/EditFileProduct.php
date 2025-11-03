<?php

namespace App\Filament\Admin\Resources\FileProducts\Pages;

use App\Filament\Admin\Resources\FileProducts\FileProductResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditFileProduct extends EditRecord
{
    protected static string $resource = FileProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
