<?php

namespace App\Filament\Resources\DeploymentLogResource\Pages;

use App\Filament\Resources\DeploymentLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeploymentLogs extends ListRecords
{
    protected static string $resource = DeploymentLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
