<?php

namespace App\Enum;

enum CategoryType: string
{
    case EVENT_ORGANIZATION_GUIDE = 'event_organization_guide';
    case VOCATIONAL_KNOWLEDGE = 'vocational_knowledge';
    case GOOD_LOCATION = 'good_location';
    case DESIGN = 'design';
    case RENTAL = 'rental';
}
