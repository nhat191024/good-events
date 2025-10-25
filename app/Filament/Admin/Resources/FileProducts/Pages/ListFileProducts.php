<?php

namespace App\Filament\Admin\Resources\FileProducts\Pages;

use App\Filament\Admin\Resources\FileProducts\FileProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFileProducts extends ListRecords
{
    protected static string $resource = FileProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
