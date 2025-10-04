<?php

namespace App\Filament\Admin\Resources\Vouchers\Pages;

use App\Filament\Admin\Resources\Vouchers\VoucherResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVoucher extends EditRecord
{
    protected static string $resource = VoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['model_id'] = 1;

        return $data;
    }
}
