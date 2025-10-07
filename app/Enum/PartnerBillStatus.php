<?php

namespace App\Enum;

enum PartnerBillStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case COMPLETED = 'completed';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('partner/bill.status_pending'),
            self::CONFIRMED => __('partner/bill.status_confirmed'),
            self::COMPLETED => __('partner/bill.status_completed'),
            self::EXPIRED => __('partner/bill.status_expired'),
            self::CANCELLED => __('partner/bill.status_cancelled'),
        };
    }
}
