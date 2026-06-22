<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Chào buổi sáng, Admin 👋';

    public function getColumns(): int | string | array
    {
        return [
            'md' => 1,
            'xl' => 3,
        ];
    }
}
