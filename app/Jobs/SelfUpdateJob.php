<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class SelfUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting self-update of Vibe Deploy...');
        
        $basePath = base_path();
        
        $commands = [
            "cd {$basePath}",
            "git pull origin main",
            "composer install --no-interaction --prefer-dist --optimize-autoloader",
            "npm install",
            "npm run build",
            "php artisan migrate --force",
            "php artisan optimize:clear",
            "php artisan queue:restart"
        ];
        
        $script = implode(' && ', $commands);
        
        $result = Process::run($script);
        
        if ($result->successful()) {
            Log::info('Self-update completed successfully.');
            Log::info($result->output());
        } else {
            Log::error('Self-update failed.');
            Log::error($result->errorOutput());
        }
    }
}
