<?php

namespace App\Services;

use App\Enums\DeploymentStatus;
use App\Models\DeploymentLog;
use App\Models\Website;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class DeployService
{
    public function deploy(Website $website, string $triggerType = 'manual'): DeploymentLog
    {
        $log = DeploymentLog::create([
            'website_id' => $website->id,
            'trigger_type' => $triggerType,
            'status' => DeploymentStatus::Running,
            'started_at' => now(),
        ]);

        $website->update([
            'status' => 'deploying',
        ]);

        try {
            // 1. Ensure root directory exists
            if (!File::exists($website->root_path)) {
                File::makeDirectory($website->root_path, 0755, true);
            }

            // 2. Clone or Pull
            if ($website->repo_url) {
                if (!File::exists($website->root_path . '/.git')) {
                    $this->runCommand("git clone -b {$website->branch} {$website->repo_url} .", $website->root_path, $log);
                } else {
                    $this->runCommand("git fetch origin", $website->root_path, $log);
                    $this->runCommand("git reset --hard origin/{$website->branch}", $website->root_path, $log);
                }
            }

            // 3. Execute build/start scripts based on type
            $this->executeDeployCommands($website, $log);

            // 4. Create Nginx config if not exists
            if ($website->auto_nginx_config) {
                $this->createNginxConfig($website, $log);
                $this->enableSite($website, $log);
            }
            
            // 5. Restart services
            if ($website->auto_reload_nginx) {
                if ($website->type->value === 'laravel') {
                    $this->runCommand("systemctl reload php{$website->php_version}-fpm", '/', $log, false);
                }
                $this->reloadNginx($log);
            }

            $log->update([
                'status' => DeploymentStatus::Success,
                'finished_at' => now(),
            ]);

            $website->update([
                'status' => 'online',
                'last_deployed_at' => now(),
            ]);

            return $log;
        } catch (\Exception $e) {
            $log->update([
                'status' => DeploymentStatus::Failed,
                'error_output' => $log->error_output . "\n\nEXCEPTION:\n" . $e->getMessage(),
                'finished_at' => now(),
            ]);

            $website->update([
                'status' => 'error',
            ]);

            return $log;
        }
    }

    protected function executeDeployCommands(Website $website, DeploymentLog $log)
    {
        $commands = [];
        $workingDir = rtrim($website->root_path, '/');
        if ($website->deploy_path && $website->deploy_path !== '/') {
            $workingDir .= '/' . trim($website->deploy_path, '/');
        }

        switch ($website->type->value) {
            case 'static':
                // Static just needs git pull, handled above.
                break;
            case 'laravel':
                if ($website->auto_install_deps) {
                    $commands[] = "composer install --no-dev --optimize-autoloader";
                    $commands[] = "npm install";
                    $commands[] = "npm run build";
                }
                if ($website->auto_migrate) {
                    $commands[] = "php artisan migrate --force";
                }
                $commands[] = "php artisan optimize:clear";
                $commands[] = "php artisan optimize";
                $commands[] = "chown -R www-data:www-data storage bootstrap/cache";
                $commands[] = "chmod -R 775 storage bootstrap/cache";
                break;
            case 'node':
            case 'next':
                if ($website->auto_install_deps) {
                    $commands[] = "npm install";
                }
                $commands[] = "npm run build";
                $appName = $website->domain;
                $commands[] = "pm2 restart {$appName} || pm2 start npm --name {$appName} -- start";
                break;
            case 'vue':
                if ($website->auto_install_deps) {
                    $commands[] = "npm install";
                }
                $commands[] = "npm run build";
                break;
        }

        // Add custom build command if any
        if ($website->build_command) {
            $commands[] = $website->build_command;
        }
        
        // Handle start command with PM2 so it doesn't block
        if ($website->start_command) {
            $appName = $website->domain . '_custom';
            $commands[] = "pm2 restart {$appName} || pm2 start \"{$website->start_command}\" --name {$appName}";
        }

        foreach ($commands as $command) {
            $this->runCommand($command, $workingDir, $log);
        }
    }

    public function createNginxConfig(Website $website, ?DeploymentLog $log = null)
    {
        $template = $this->getNginxTemplate($website);
        $confPath = "/etc/nginx/sites-available/{$website->domain}";
        
        // In local/testing mode, we can't write to /etc/nginx. 
        // We'll write to a local stub directory if /etc/nginx doesn't exist.
        if (!File::exists('/etc/nginx/sites-available')) {
            $localDir = storage_path('nginx/sites-available');
            File::ensureDirectoryExists($localDir);
            $confPath = $localDir . "/{$website->domain}";
            if ($log) $this->appendLog($log, "Mocking Nginx config at $confPath");
        }

        File::put($confPath, $template);
        if ($log) $this->appendLog($log, "Nginx config created for {$website->domain}");
    }

    public function enableSite(Website $website, ?DeploymentLog $log = null)
    {
        $available = "/etc/nginx/sites-available/{$website->domain}";
        $enabled = "/etc/nginx/sites-enabled/{$website->domain}";

        if (!File::exists('/etc/nginx/sites-enabled')) {
            $enabled = storage_path("nginx/sites-enabled/{$website->domain}");
            File::ensureDirectoryExists(storage_path('nginx/sites-enabled'));
            $available = storage_path("nginx/sites-available/{$website->domain}");
        }

        if (!File::exists($enabled)) {
            $this->runCommand("ln -s {$available} {$enabled}", '/', $log, false);
            if ($log) $this->appendLog($log, "Site enabled: {$website->domain}");
        }
    }

    public function reloadNginx(?DeploymentLog $log = null)
    {
        // Ignore error if nginx doesn't exist locally
        $this->runCommand("nginx -s reload || systemctl reload nginx", '/', $log, false);
    }

    public function installSsl(Website $website)
    {
        $log = DeploymentLog::create([
            'website_id' => $website->id,
            'trigger_type' => 'manual',
            'status' => DeploymentStatus::Running,
            'started_at' => now(),
        ]);

        try {
            $this->runCommand("certbot --nginx -d {$website->domain} --non-interactive --agree-tos -m admin@{$website->domain}", '/', $log);
            $website->update(['ssl_enabled' => true]);
            $log->update(['status' => DeploymentStatus::Success, 'finished_at' => now()]);
        } catch (\Exception $e) {
            $log->update(['status' => DeploymentStatus::Failed, 'error_output' => $e->getMessage(), 'finished_at' => now()]);
        }
    }

    protected function runCommand(string $command, string $cwd = '/', ?DeploymentLog $log = null, bool $throwOnError = true)
    {
        if ($log) {
            $this->appendLog($log, "$cwd> $command");
        }

        $result = Process::path($cwd)->run($command);

        if ($log) {
            if ($result->output()) {
                $this->appendLog($log, $result->output());
            }
            if ($result->errorOutput()) {
                $this->appendErrorLog($log, $result->errorOutput());
            }
        }

        if (!$result->successful() && $throwOnError) {
            throw new \Exception("Command failed: $command\n" . $result->errorOutput());
        }

        return $result;
    }

    protected function appendLog(DeploymentLog $log, string $message)
    {
        $log->output = $log->output . $message . "\n";
        $log->save();
    }

    protected function appendErrorLog(DeploymentLog $log, string $message)
    {
        $log->error_output = $log->error_output . $message . "\n";
        $log->save();
    }

    protected function getNginxTemplate(Website $website): string
    {
        $serverNames = [$website->domain];
        // Only add www if it's a top level domain (1 dot)
        if (substr_count($website->domain, '.') === 1) {
            $serverNames[] = 'www.' . $website->domain;
        }
        $serverNameString = implode(' ', $serverNames);

        $root = rtrim($website->root_path, '/');
        if ($website->deploy_path && $website->deploy_path !== '/') {
            $root .= '/' . trim($website->deploy_path, '/');
        }

        $redirectBlock = '';
        // Removed redirect block logic for now as it relied on non-existent columns
        
        if ($website->type->value === 'laravel') {
            $publicDir = trim($website->public_dir ?: 'public', '/');
            $root .= '/' . $publicDir;
            $mainBlock = "server {
    listen 80;
    server_name {$serverNameString};
    root {$root};

    add_header X-Frame-Options \"SAMEORIGIN\";
    add_header X-Content-Type-Options \"nosniff\";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location ~ /\.ht {
        deny all;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php{$website->php_version}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    access_log /var/log/nginx/{$website->id}_access.log;
    error_log /var/log/nginx/{$website->id}_error.log;
}";
            return $redirectBlock . "\n" . $mainBlock;
        } elseif ($website->type->value === 'vue') {
            $root .= '/dist';
            $mainBlock = "server {
    listen 80;
    server_name {$serverNameString};
    root {$root};
    index index.html;
    location / {
        try_files \$uri \$uri/ /index.html;
    }
}";
            return $redirectBlock . "\n" . $mainBlock;
        } elseif (in_array($website->type->value, ['node', 'next'])) {
            // Hardcoded port 3000 for MVP, ideal is to fetch available port
            $mainBlock = "server {
    listen 80;
    server_name {$serverNameString};
    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host \$host;
        proxy_cache_bypass \$http_upgrade;
    }
}";
            return $redirectBlock . "\n" . $mainBlock;
        }

        // Static
        $mainBlock = "server {
    listen 80;
    server_name {$serverNameString};
    root {$root};
    index index.html index.htm;
    location / {
        try_files \$uri \$uri/ =404;
    }
}";
        return $redirectBlock . "\n" . $mainBlock;
    }
}
