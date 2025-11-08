<?php

namespace App\Filament\Admin\Resources\RentalCategories\Pages;

use App\Filament\Admin\Resources\RentalCategories\RentalCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRentalCategory extends EditRecord
{
    protected static string $resource = RentalCategoryResource::class;
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('global.hidden')),
            RestoreAction::make(),
        ];
    }
}
