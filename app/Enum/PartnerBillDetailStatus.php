<?php

namespace App\Enum;

enum PartnerBillDetailStatus: string
{
    case NEW = 'new';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::NEW => __('partner/bill_detail.status_new'),
            self::CLOSED => __('partner/bill_detail.status_closed'),
            self::CANCELLED => __('partner/bill_detail.status_cancelled'),
        };
    }
}
