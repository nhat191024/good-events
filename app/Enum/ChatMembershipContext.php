<?php

namespace App\Enum;

enum ChatMembershipContext: string
{
    case Client = 'client';
    case Partner = 'partner';
    case Invitation = 'invitation';
    case System = 'system';
}
