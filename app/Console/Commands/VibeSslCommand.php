<?php

namespace App\Console\Commands;

use App\Jobs\InstallSslJob;
use App\Models\Website;
use Illuminate\Console\Command;

class VibeSslCommand extends Command
{
    protected $signature = 'vibe:ssl {website_id}';
    protected $description = 'Trigger SSL installation for a specific website';

    public function handle()
    {
        $websiteId = $this->argument('website_id');
        $website = Website::findOrFail($websiteId);

        $this->info("Dispatching SSL install job for {$website->domain}...");
        InstallSslJob::dispatch($website);
        $this->info("Job dispatched successfully.");
    }
}
