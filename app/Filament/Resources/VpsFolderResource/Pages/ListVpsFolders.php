<?php

namespace App\Filament\Resources\VpsFolderResource\Pages;

use App\Filament\Resources\VpsFolderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVpsFolders extends ListRecords
{
    protected static string $resource = VpsFolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
