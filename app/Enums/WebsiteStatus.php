<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum WebsiteStatus: string implements HasLabel, HasColor
{
    case Pending = 'pending';
    case Online = 'online';
    case Deploying = 'deploying';
    case Error = 'error';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'Chờ xử lý',
            self::Online => 'Trực tuyến',
            self::Deploying => 'Đang triển khai',
            self::Error => 'Gặp lỗi',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Online => 'success',
            self::Deploying => 'primary',
            self::Error => 'danger',
        };
    }
}
