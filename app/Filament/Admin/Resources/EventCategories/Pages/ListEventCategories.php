<?php

namespace App\Filament\Admin\Resources\EventCategories\Pages;

use App\Filament\Admin\Resources\EventCategories\EventCategoryResource;
use App\Enum\CacheKey;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

use Illuminate\Support\Facades\Cache;

class ListEventCategories extends ListRecords
{
    protected static string $resource = EventCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function reorderTable(array $order, string|int|null $draggedRecordKey = null): void
    {
        parent::reorderTable($order);

        Cache::forget(CacheKey::PARTNER_CATEGORIES->value);
        Cache::forget(CacheKey::PARTNER_CATEGORIES_TREE->value);
        Cache::forget(CacheKey::PARTNER_CATEGORIES_ALL->value);
    }
}
