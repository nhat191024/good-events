<?php

namespace App\Filament\Admin\Resources\VocationalKnowledge\Pages;

use App\Filament\Admin\Resources\VocationalKnowledge\VocationalKnowledgeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVocationalKnowledge extends ListRecords
{
    protected static string $resource = VocationalKnowledgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
