<?php

namespace App\Filament\Admin\Resources\EventCategories\Pages;

use App\Filament\Admin\Resources\EventCategories\EventCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEventCategory extends EditRecord
{
    protected static string $resource = EventCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('global.hidden'))
                ->disabled(fn($record): bool => $record->children()->exists()),
        ];
    }

    public function getTitle(): string
    {
        return __('admin/partnerCategory.edit_partner_category_with_name', ['name' => $this->getRecord()->name]);
    }

    public function getBreadcrumb(): string
    {
        return $this->getRecord()->name;
    }
}
