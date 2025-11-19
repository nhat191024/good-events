<?php

namespace App\Enum;

enum BannerType: string
{
    case PARTNER = 'partner';
    case DESIGN = 'design';
    case RENTAL = 'rental';
    case PARTNER_MOBILE = 'mobile_partner';
    case DESIGN_MOBILE = 'mobile_design';
    case RENTAL_MOBILE = 'mobile_rental';
}
