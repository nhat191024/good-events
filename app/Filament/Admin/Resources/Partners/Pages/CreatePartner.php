<?php

namespace App\Filament\Admin\Resources\Partners\Pages;

use App\Filament\Admin\Resources\Partners\PartnerResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePartner extends CreateRecord
{
    protected static string $resource = PartnerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove partner profile fields from main data as they will be handled separately
        unset($data['partner_name'], $data['identity_card_number']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Handle partner profile creation
        $formData = $this->form->getState();

        if (isset($formData['partner_name']) || isset($formData['identity_card_number'])) {
            $this->record->partnerProfile()->create([
                'partner_name' => $formData['partner_name'] ?? '',
                'identity_card_number' => $formData['identity_card_number'] ?? '',
            ]);
        }
    }
}
