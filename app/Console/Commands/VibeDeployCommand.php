<?php

namespace App\Console\Commands;

use App\Jobs\DeployWebsiteJob;
use App\Models\Website;
use Illuminate\Console\Command;

class VibeDeployCommand extends Command
{
    protected $signature = 'vibe:deploy {website_id}';
    protected $description = 'Trigger a deploy for a specific website';

    public function handle()
    {
        $websiteId = $this->argument('website_id');
        $website = Website::findOrFail($websiteId);

        $this->info("Dispatching deploy job for {$website->domain}...");
        DeployWebsiteJob::dispatch($website, 'manual');
        $this->info("Job dispatched successfully.");
    }
}
