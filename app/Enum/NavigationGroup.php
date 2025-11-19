<?php

namespace App\Enum;

enum NavigationGroup: string
{
    case CATEGORIES = 'categories';
    case PRODUCTS = 'products';
    case BLOG = 'blog';
    case USER_MANAGEMENT = 'user_management';
    case BILLING = 'billing';
    case SYSTEM = 'system';
    case SETTINGS = 'settings';
}
