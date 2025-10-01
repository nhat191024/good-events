<?php

namespace App\Filament\Admin\Resources\EventCategories\Pages;

use App\Filament\Admin\Resources\EventCategories\EventCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEventCategory extends CreateRecord
{
    protected static string $resource = EventCategoryResource::class;
}
