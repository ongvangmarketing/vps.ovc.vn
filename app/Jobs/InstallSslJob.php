<?php

namespace App\Jobs;

use App\Models\Website;
use App\Services\DeployService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InstallSslJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 mins

    /**
     * Create a new job instance.
     */
    public function __construct(public Website $website)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(DeployService $deployService): void
    {
        $deployService->installSsl($this->website);
    }
}
