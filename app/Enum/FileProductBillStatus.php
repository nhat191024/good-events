<?php

namespace App\Enum;

enum FileProductBillStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
}
