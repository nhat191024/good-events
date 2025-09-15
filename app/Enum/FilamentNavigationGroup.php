<?php

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;

enum FilamentNavigationGroup implements HasLabel
{
    case USER_MANAGEMENT;
    case BILLING;

    public function getLabel(): string
    {
        return match ($this) {
            self::USER_MANAGEMENT => __('admin\sidebar.user_management'),
            self::BILLING => __('admin\sidebar.billing'),
        };
    }
}
