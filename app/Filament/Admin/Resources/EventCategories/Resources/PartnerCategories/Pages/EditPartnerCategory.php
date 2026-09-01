<?php

namespace App\Filament\Admin\Resources\EventCategories\Resources\PartnerCategories\Pages;

use App\Filament\Admin\Resources\EventCategories\EventCategoryResource;
use App\Filament\Admin\Resources\EventCategories\Pages\ManagePartnerCategory;
use App\Filament\Admin\Resources\EventCategories\Resources\PartnerCategories\PartnerCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPartnerCategory extends EditRecord
{
    protected static string $resource = PartnerCategoryResource::class;

    /** @var list<string> */
    private array $accessoryNames = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['accessory_names'] = $this->record->accessories()->pluck('name')->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->accessoryNames = array_values($data['accessory_names'] ?? []);
        unset($data['accessory_names']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->accessories()->whereNotIn('name', $this->accessoryNames)->delete();

        foreach ($this->accessoryNames as $name) {
            $this->record->accessories()->firstOrCreate(['name' => $name]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('global.hidden'))
                ->disabled(fn ($record): bool => $record->partnerServices()->exists()),
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
