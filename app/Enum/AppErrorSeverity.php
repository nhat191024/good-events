<?php

namespace App\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AppErrorSeverity: string implements HasColor, HasLabel
{
    case Debug = 'debug';
    case Info = 'info';
    case Warning = 'warning';
    case Error = 'error';
    case Fatal = 'fatal';

    public function getLabel(): string
    {
        return match ($this) {
            self::Debug => 'Debug',
            self::Info => 'Thông tin',
            self::Warning => 'Cảnh báo',
            self::Error => 'Lỗi',
            self::Fatal => 'Nghiêm trọng',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Debug => 'gray',
            self::Info => 'info',
            self::Warning => 'warning',
            self::Error => 'danger',
            self::Fatal => 'danger',
        };
    }
}
