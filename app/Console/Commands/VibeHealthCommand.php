<?php

namespace App\Console\Commands;

use App\Jobs\CheckServerHealthJob;
use Illuminate\Console\Command;

class VibeHealthCommand extends Command
{
    protected $signature = 'vibe:health';
    protected $description = 'Trigger server health check';

    public function handle()
    {
        $this->info("Dispatching health check job...");
        CheckServerHealthJob::dispatch();
        $this->info("Job dispatched successfully.");
    }
}
