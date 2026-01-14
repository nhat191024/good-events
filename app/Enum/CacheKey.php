<?php

namespace App\Enum;

enum CacheKey: string
{
    case PARTNER_CATEGORIES = 'partner_categories';
    case PARTNER_CATEGORIES_TREE = 'partner_categories_tree';
    case PARTNER_CATEGORIES_ALL = 'partner_categories_all';
    case PARTNER_SERVICES = 'partner_services';

    //FileProduct Discover Page
    case FILE_DISCOVER_CATEGORIES_SIDEBAR = 'discover_categories_sidebar';
    case FILE_DISCOVER_TAGS_SIDEBAR = 'discover_tags_sidebar';
}
