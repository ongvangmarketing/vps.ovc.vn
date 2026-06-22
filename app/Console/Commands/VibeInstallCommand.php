<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class VibeInstallCommand extends Command
{
    protected $signature = 'vibe:install';
    protected $description = 'Install and configure VPS OVC on VPS';

    public function handle()
    {
        $this->info('Starting VPS OVC Installation...');

        // 1. Create root directory
        $rootPath = '/var/www/sites';
        if (!File::exists($rootPath)) {
            $this->info("Creating directory: $rootPath");
            try {
                File::makeDirectory($rootPath, 0755, true);
            } catch (\Exception $e) {
                $this->error("Could not create $rootPath. Run as root or sudo.");
            }
        } else {
            $this->line("$rootPath already exists.");
        }

        // 2. Storage link
        $this->call('storage:link');

        // 3. Print Nginx configuration
        $this->info("\n--- Nginx Configuration for deploy.ovc.vn ---");
        $this->line("Please create an Nginx config file at /etc/nginx/sites-available/deploy.ovc.vn");
        $this->line("And paste the following content:\n");

        $path = base_path('public');
        $this->line("server {");
        $this->line("    listen 80;");
        $this->line("    server_name deploy.ovc.vn;");
        $this->line("    root {$path};");
        $this->line("    index index.php;");
        $this->line("    location / {");
        $this->line("        try_files \$uri \$uri/ /index.php?\$query_string;");
        $this->line("    }");
        $this->line("    location ~ \.php$ {");
        $this->line("        include snippets/fastcgi-php.conf;");
        $this->line("        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;");
        $this->line("    }");
        $this->line("}\n");

        $this->info("Then run:");
        $this->line("sudo ln -s /etc/nginx/sites-available/deploy.ovc.vn /etc/nginx/sites-enabled/");
        $this->line("sudo nginx -t && sudo systemctl reload nginx");
        $this->line("sudo certbot --nginx -d deploy.ovc.vn");

        $this->info("\nInstallation complete! Use 'php artisan vibe:create-admin' to create an admin user.");
    }
}
