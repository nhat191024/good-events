<?php

namespace App\Enum;

enum PartnerBillStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
}
