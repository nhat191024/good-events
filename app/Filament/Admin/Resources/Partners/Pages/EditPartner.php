<?php

namespace App\Filament\Admin\Resources\Partners\Pages;

use App\Filament\Admin\Resources\Partners\PartnerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPartner extends EditRecord
{
    protected static string $resource = PartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('global.ban')),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record->partnerProfile) {
            $data['partner_name'] = $this->record->partnerProfile->partner_name;
            $data['identity_card_number'] = $this->record->partnerProfile->identity_card_number;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['partner_name'], $data['identity_card_number']);

        return $data;
    }

    protected function afterSave(): void
    {
        $formData = $this->form->getState();

        if (isset($formData['partner_name']) || isset($formData['identity_card_number'])) {
            $this->record->partnerProfile()->updateOrCreate(
                ['user_id' => $this->record->id],
                [
                    'partner_name' => $formData['partner_name'] ?? '',
                    'identity_card_number' => $formData['identity_card_number'] ?? '',
                ]
            );
        }
    }
}
