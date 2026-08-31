<?php

namespace App\Enum;

enum PartnerBillPriceIncreaseRequestStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Superseded = 'superseded';
    case Cancelled = 'cancelled';
}
