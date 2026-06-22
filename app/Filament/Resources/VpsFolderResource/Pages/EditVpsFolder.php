<?php

namespace App\Filament\Resources\VpsFolderResource\Pages;

use App\Filament\Resources\VpsFolderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVpsFolder extends EditRecord
{
    protected static string $resource = VpsFolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
