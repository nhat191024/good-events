<?php

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;

enum AppErrorType: string implements HasLabel
{
    case Api = 'api';
    case Runtime = 'runtime';
    case Network = 'network';
    case Validation = 'validation';
    case Authentication = 'authentication';
    case Ui = 'ui';
    case Performance = 'performance';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Api => 'API',
            self::Runtime => 'Runtime',
            self::Network => 'Mạng',
            self::Validation => 'Dữ liệu không hợp lệ',
            self::Authentication => 'Xác thực',
            self::Ui => 'Giao diện',
            self::Performance => 'Hiệu năng',
            self::Other => 'Khác',
        };
    }
}
