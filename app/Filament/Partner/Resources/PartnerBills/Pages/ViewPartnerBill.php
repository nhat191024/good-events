<?php

namespace App\Filament\Partner\Resources\PartnerBills\Pages;

use App\Filament\Partner\Resources\PartnerBills\PartnerBillResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPartnerBill extends ViewRecord
{
    protected static string $resource = PartnerBillResource::class;

    public function getTitle(): string
    {
        return __('partner/bill.view_details');
    }
}
