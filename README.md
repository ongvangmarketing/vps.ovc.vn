# VPS OVC

VPS OVC is a Laravel 12 + Filament 4 based MVP system that automates deploying static, Laravel, Node.js, Next.js, and Vue.js websites on an Ubuntu VPS.

## 1. VPS Requirements
- OS: Ubuntu 22.04 / 24.04 LTS
- RAM: Minimum 1GB (2GB+ Recommended)
- Root or sudo access

## 2. Server Prerequisites Installation
Login to your VPS via SSH and install the required packages:

```bash
sudo apt update && sudo apt upgrade -y

# Install Nginx, MySQL, Git, Curl, Unzip
sudo apt install nginx mysql-server git curl unzip certbot python3-certbot-nginx -y

# Install PHP 8.3 & FPM
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.3 php8.3-fpm php8.3-mysql php8.3-xml php8.3-curl php8.3-zip php8.3-mbstring php8.3-bcmath -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js 20.x
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install PM2 globally
sudo npm install -g pm2
```

## 3. Clone and Setup VPS OVC
Clone the project into `/var/www/vpsovc`:

```bash
cd /var/www
git clone <your-repo-url> vpsovc
cd vpsovc

composer install --optimize-autoloader --no-dev
npm install
npm run build
```

## 4. Configuration
Create the `.env` file and set up your database connection:

```bash
cp .env.example .env
nano .env

# Set:
# DB_CONNECTION=mysql
# DB_DATABASE=vps_ovc
# DB_USERNAME=root
# DB_PASSWORD=your_password
```

Create the database in MySQL:
```bash
mysql -u root -p -e "CREATE DATABASE vps_ovc;"
```

## 5. Run Migrations & Setup
```bash
php artisan key:generate
php artisan migrate --force

# Run the installer command to setup directories
php artisan vibe:install
```

## 6. Create Admin User
```bash
php artisan vibe:create-admin
```

## 7. Configure Nginx for VPS OVC
Follow the output from `php artisan vibe:install` to create the Nginx configuration. It will look like this:

```nginx
server {
    listen 80;
    server_name deploy.ovc.vn;
    root /var/www/vpsovc/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }
}
```

Enable the site and reload Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/deploy.ovc.vn /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 8. Install SSL for the Panel
```bash
sudo certbot --nginx -d deploy.ovc.vn
```

## 9. Configure Queue Worker (Supervisor)
To process deployments in the background, set up a Supervisor worker:

```bash
sudo apt install supervisor -y
sudo nano /etc/supervisor/conf.d/vps-ovc-worker.conf
```

Add the following:
```ini
[program:vps-ovc-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/vpsovc/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=root
numprocs=1
stdout_logfile=/var/www/vpsovc/storage/logs/worker.log
stopwaitsecs=3600
```

Start the worker:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start vps-ovc-worker:*
```

## 10. Adding Your First Website
1. Go to `https://deploy.ovc.vn/admin`
2. Login with your admin credentials
3. Navigate to **Deploy Website** -> **New Website**
4. Choose **Git URL**, enter your repo (e.g. `https://github.com/your/repo.git`)
5. Configure the root path and domain
6. Click **Deploy Now**

## 11. GitHub Webhook Setup
1. In your GitHub repository, go to **Settings > Webhooks**
2. Add a new webhook:
   - Payload URL: `https://deploy.ovc.vn/webhook/github/{your-domain}`
   - Content type: `application/json`
   - Secret: (Paste the webhook secret from your VPS OVC panel)
3. Now, whenever you push code, VPS OVC will automatically pull and build.

## 12. VPS OVC Self-Update (CI/CD)
To automatically update the VPS OVC panel itself when you push code to GitHub from your Mac:
1. In your VPS OVC GitHub repository, go to **Settings > Webhooks**
2. Add a new webhook:
   - Payload URL: `https://deploy.ovc.vn/webhook/self-update?token=vibe-secret-token` (Replace `vibe-secret-token` with the `SELF_UPDATE_TOKEN` in your VPS `.env` file).
   - Content type: `application/json`
3. Now, whenever you push code to `main`, the VPS will automatically run `git pull`, `composer install`, `npm run build`, and restart the queue.

## 13. Troubleshooting
- **Permission Denied**: Ensure `storage` and `bootstrap/cache` are writable. VPS OVC worker runs as `root` to manage Nginx and PM2, so ensure permissions are aligned.
- **Queue not processing**: Run `php artisan queue:work` manually to see if jobs are failing.
- **Nginx errors**: Check `/var/log/nginx/error.log` and verify the generated `nginx.conf` in `/etc/nginx/sites-available/`.
