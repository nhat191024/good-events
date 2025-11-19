<?php

namespace App\Enum;

enum Role: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case PARTNER = 'partner';
    case CLIENT = 'client';
}
