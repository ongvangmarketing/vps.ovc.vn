<?php

namespace App\Filament\Widgets;

use App\Models\Server;
use Filament\Widgets\Widget;

class ServerUsageCard extends Widget
{
    protected static string $view = 'filament.widgets.server-usage-card';
    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public ?Server $server = null;

    public function mount()
    {
        // For MVP, we just get the first server (the local VPS)
        $this->server = Server::first();
    }
}
