<?php

namespace App\Enum;

enum PartnerServiceStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
