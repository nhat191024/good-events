<?php

namespace App\Filament\Admin\Resources\VocationalKnowledge\Pages;

use App\Filament\Admin\Resources\VocationalKnowledge\VocationalKnowledgeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVocationalKnowledge extends CreateRecord
{
    protected static string $resource = VocationalKnowledgeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['type'] = 'vocational_knowledge';
        return $data;
    }
}
