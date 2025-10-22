<?php

namespace App\Filament\Admin\Resources\Categories\Resources\ChildrenCategories\Pages;

use App\Filament\Admin\Resources\Categories\Resources\ChildrenCategories\ChildrenCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditChildrenCategory extends EditRecord
{
    protected static string $resource = ChildrenCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
