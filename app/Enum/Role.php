<?php

namespace App\Enum;

enum Role: string
{
    case ADMIN = 'admin';
    case PARTNER = 'partner';
    case USER = 'user';
}
