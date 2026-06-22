<?php

namespace App\Jobs;

use App\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;

class CheckServerHealthJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $server = Server::first();
        if (!$server) {
            $server = Server::create(['name' => 'Local VPS', 'ip' => '127.0.0.1']);
        }

        try {
            // Get CPU Usage (Linux)
            $cpuResult = Process::run("top -bn1 | grep 'Cpu(s)' | awk '{print $2 + $4}'");
            $cpu = trim($cpuResult->output());

            // Get RAM Usage
            $ramResult = Process::run("free -m | awk 'NR==2{printf \"%.2f%%\", $3*100/$2 }'");
            $ram = trim($ramResult->output());

            // Get Disk Usage
            $diskResult = Process::run("df -h / | awk 'NR==2{print $5}'");
            $disk = trim($diskResult->output());

            // Check services
            $nginxResult = Process::run("systemctl is-active nginx");
            $nginxStatus = trim($nginxResult->output()) === 'active' ? 'active' : 'offline';

            $mysqlResult = Process::run("systemctl is-active mysql");
            $mysqlStatus = trim($mysqlResult->output()) === 'active' ? 'active' : 'offline';

            $server->update([
                'cpu_usage' => $cpu ? $cpu . '%' : null,
                'ram_usage' => $ram ?: null,
                'disk_usage' => $disk ?: null,
                'nginx_status' => $nginxStatus,
                'mysql_status' => $mysqlStatus,
                'last_checked_at' => now(),
            ]);
        } catch (\Exception $e) {
            $server->update(['status' => 'offline', 'last_checked_at' => now()]);
        }
    }
}
