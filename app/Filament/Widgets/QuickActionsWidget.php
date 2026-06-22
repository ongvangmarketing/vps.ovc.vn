<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions-widget';
    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];
}
