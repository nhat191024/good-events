<?php

namespace App\Enum;

enum CacheKey: string
{
    case PARTNER_CATEGORIES = 'partner_categories';
    case PARTNER_CATEGORIES_TREE = 'partner_categories_tree';
    case PARTNER_CATEGORIES_ALL = 'partner_categories_all';
    case PARTNER_SERVICES = 'partner_services';
}
