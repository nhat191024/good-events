<?php

namespace App\Filament\Admin\Resources\VocationalKnowledge\Pages;

use App\Filament\Admin\Resources\VocationalKnowledge\VocationalKnowledgeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditVocationalKnowledge extends EditRecord
{
    protected static string $resource = VocationalKnowledgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('global.hidden')),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
