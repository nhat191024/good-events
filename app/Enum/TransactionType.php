<?php

namespace App\Enum;

enum TransactionType: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAWAL = 'withdrawal';

    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT => __('partner/transaction.types.deposit'),
            self::WITHDRAWAL => __('partner/transaction.types.withdrawal'),
        };
    }
}
