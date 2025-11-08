<?php

namespace App\Filament\Admin\Resources\DesignCategories\Pages;

use App\Filament\Admin\Resources\DesignCategories\DesignCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDesignCategory extends CreateRecord
{
    protected static string $resource = DesignCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'design';

        return $data;
    }
}
