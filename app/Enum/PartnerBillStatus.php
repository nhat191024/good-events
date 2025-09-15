<?php

namespace App\Enum;

enum PartnerBillStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('partner/bill.status_pending'),
            self::PAID => __('partner/bill.status_paid'),
            self::CANCELLED => __('partner/bill.status_cancelled'),
        };
    }
}
