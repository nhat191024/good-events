<?php

namespace App\Filament\Admin\Resources\EventCategories\Resources\PartnerCategories\Pages;

use App\Filament\Admin\Resources\EventCategories\Resources\PartnerCategories\PartnerCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePartnerCategory extends CreateRecord
{
    protected static string $resource = PartnerCategoryResource::class;
}
