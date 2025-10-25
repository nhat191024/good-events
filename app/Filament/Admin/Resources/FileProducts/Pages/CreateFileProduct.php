<?php

namespace App\Filament\Admin\Resources\FileProducts\Pages;

use App\Filament\Admin\Resources\FileProducts\FileProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFileProduct extends CreateRecord
{
    protected static string $resource = FileProductResource::class;
}
