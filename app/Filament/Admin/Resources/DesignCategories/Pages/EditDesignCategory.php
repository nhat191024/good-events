<?php

namespace App\Filament\Admin\Resources\DesignCategories\Pages;

use App\Filament\Admin\Resources\DesignCategories\DesignCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditDesignCategory extends EditRecord
{
    protected static string $resource = DesignCategoryResource::class;
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('global.hidden'))
                ->disabled(fn($record): bool => $record->children()->exists()),
            RestoreAction::make(),
        ];
    }
}
