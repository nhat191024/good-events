<?php

namespace App\Filament\Admin\Resources\DesignCategories\Pages;

use App\Filament\Admin\Resources\DesignCategories\DesignCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDesignCategories extends ListRecords
{
    protected static string $resource = DesignCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
