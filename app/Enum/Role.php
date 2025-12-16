<?php

namespace App\Enum;

enum Role: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case PARTNER = 'partner';
    case CLIENT = 'client';
    case HUMAN_RESOURCE_MANAGER = 'human_resource_manager';
    case DESIGN_MANAGER = 'design_manager';
    case RENTAL_MANAGER = 'rental_manager';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => __('global.role.super_admin'),
            self::ADMIN => __('global.role.admin'),
            self::PARTNER => __('global.role.partner'),
            self::CLIENT => __('global.role.client'),
            self::HUMAN_RESOURCE_MANAGER => __('global.role.human_resource_manager'),
            self::DESIGN_MANAGER => __('global.role.design_manager'),
            self::RENTAL_MANAGER => __('global.role.rental_manager'),
        };
    }
}
