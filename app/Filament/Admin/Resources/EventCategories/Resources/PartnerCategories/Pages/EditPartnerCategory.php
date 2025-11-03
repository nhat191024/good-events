<?php

namespace App\Filament\Admin\Resources\EventCategories\Resources\PartnerCategories\Pages;

use App\Filament\Admin\Resources\EventCategories\Resources\PartnerCategories\PartnerCategoryResource;
use App\Filament\Admin\Resources\EventCategories\Pages\ManagePartnerCategory;
use App\Filament\Admin\Resources\EventCategories\EventCategoryResource;

use Filament\Resources\Pages\EditRecord;

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;

class EditPartnerCategory extends EditRecord
{
    protected static string $resource = PartnerCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('global.hidden'))
                ->disabled(fn($record): bool => $record->partnerServices()->exists()),
            RestoreAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return __('admin/partnerCategory.edit_partner_category_with_name', ['name' => $this->getRecord()->name]);
    }

    public function getBreadcrumbs(): array
    {
        return [
            EventCategoryResource::getIndexUrl() => __('admin/partnerCategory.plural'),
            ManagePartnerCategory::getUrl([$this->getRecord()->parent_id]) => $this->getParentRecord()->name,
            $this->getRecord()->name,
        ];
    }
}
