<?php

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;

enum FilamentNavigationGroup implements HasLabel
{
    case PRODUCTS;
    case CATEGORIES;
    case USER_MANAGEMENT;
    case BILLING;
    case SYSTEM;
    case SETTINGS;

    public function getLabel(): string
    {
        return match ($this) {
            self::PRODUCTS => __('admin/sidebar.products'),
            self::USER_MANAGEMENT => __('admin/sidebar.user_management'),
            self::BILLING => __('admin/sidebar.billing'),
            self::SETTINGS => __('admin/sidebar.settings'),
            self::CATEGORIES => __('admin/sidebar.categories'),
            self::SYSTEM => __('admin/sidebar.system'),
        };
    }
}
