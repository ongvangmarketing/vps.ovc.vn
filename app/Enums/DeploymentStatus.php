<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum DeploymentStatus: string implements HasLabel, HasColor
{
    case Running = 'running';
    case Success = 'success';
    case Failed = 'failed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Running => 'Đang chạy',
            self::Success => 'Thành công',
            self::Failed => 'Thất bại',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Running => 'primary',
            self::Success => 'success',
            self::Failed => 'danger',
        };
    }
}
