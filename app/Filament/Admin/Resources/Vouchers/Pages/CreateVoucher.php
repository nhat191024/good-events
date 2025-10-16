<?php

namespace App\Filament\Admin\Resources\Vouchers\Pages;

use App\Filament\Admin\Resources\Vouchers\VoucherResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVoucher extends CreateRecord
{
    protected static string $resource = VoucherResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['model_id'] = 1;
        $data['data']['times_used'] = 0;

        return $data;
    }
}
