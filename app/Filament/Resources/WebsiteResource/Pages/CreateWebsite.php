<?php

namespace App\Filament\Resources\WebsiteResource\Pages;

use App\Filament\Resources\WebsiteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWebsite extends CreateRecord
{
    protected static string $resource = WebsiteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['vps_folder_id']) && !empty($data['new_folder_slug'])) {
            $folder = \App\Models\VpsFolder::create([
                'name' => $data['new_folder_name'],
                'slug' => $data['new_folder_slug'],
                'type' => $data['new_folder_type'] ?? null,
                'description' => $data['new_folder_desc'] ?? null,
                'path' => '/var/www/sites/' . $data['new_folder_slug'],
            ]);
            $data['vps_folder_id'] = $folder->id;
        }

        if (!empty($data['vps_folder_id'])) {
            $folder = \App\Models\VpsFolder::find($data['vps_folder_id']);
            if ($folder) {
                $data['root_path'] = $folder->path . '/' . $data['domain'];
            }
        } else {
            $data['root_path'] = '/var/www/sites/' . $data['domain'];
        }

        unset($data['new_folder_name']);
        unset($data['new_folder_slug']);
        unset($data['new_folder_type']);
        unset($data['new_folder_desc']);

        $data['status'] = \App\Enums\WebsiteStatus::Deploying;
        $data['webhook_secret'] = \Illuminate\Support\Str::random(32);

        return $data;
    }
    
    protected function afterCreate(): void
    {
        \Illuminate\Support\Facades\Artisan::call('vibe:deploy', ['website_id' => $this->record->id]);
        if ($this->record->ssl_enabled) {
            \Illuminate\Support\Facades\Artisan::call('vibe:ssl', ['website_id' => $this->record->id]);
        }
    }
}
