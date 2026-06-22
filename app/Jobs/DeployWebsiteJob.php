<?php

namespace App\Jobs;

use App\Models\Website;
use App\Services\DeployService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeployWebsiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Website $website,
        public string $triggerType = 'manual'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(DeployService $deployService): void
    {
        $deployService->deploy($this->website, $this->triggerType);
    }
}
