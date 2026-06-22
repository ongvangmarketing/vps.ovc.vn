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
                'description' => $data['new_folder_description'] ?? null,
                'path' => '/var/www/sites/' . $data['new_folder_slug'],
            ]);
            $data['vps_folder_id'] = $folder->id;
        }

        if (empty($data['domain']) && !empty($data['subdomain']) && !empty($data['base_domain'])) {
            $data['domain'] = $data['subdomain'] . '.' . $data['base_domain'];
        }

        if (!empty($data['vps_folder_id'])) {
            $folder = \App\Models\VpsFolder::find($data['vps_folder_id']);
            if ($folder) {
                $data['root_path'] = $folder->path . '/' . $data['domain'];
            }
        } else {
            $data['root_path'] = '/var/www/sites/' . $data['domain'];
        }

        $fieldsToUnset = [
            'new_folder_name', 'new_folder_slug', 'new_folder_type', 'new_folder_description', 'new_folder_desc',
            'source_type', 'zip_file', 'git_repo_url', 'raw_html', 'deploy_path',
            'subdomain', 'base_domain', 'auto_attach_domain', 'redirect_subdomain', 'preview_root_path'
        ];

        foreach ($fieldsToUnset as $field) {
            unset($data[$field]);
        }

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
