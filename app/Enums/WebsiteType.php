<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum WebsiteType: string implements HasLabel, HasColor
{
    case Static = 'static';
    case Laravel = 'laravel';
    case Node = 'node';
    case Next = 'next';
    case Vue = 'vue';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Static => 'Static HTML',
            self::Laravel => 'Laravel',
            self::Node => 'Node.js',
            self::Next => 'Next.js',
            self::Vue => 'Vue.js',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Static => 'gray',
            self::Laravel => 'danger',
            self::Node => 'success',
            self::Next => 'info',
            self::Vue => 'success',
        };
    }
}
