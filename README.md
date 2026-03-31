# SEO Tracking Dashboard

A private internal SEO tracking dashboard built with Laravel 12 to fetch and monitor daily Google Search Console metrics.

## Requirements
- Docker and Docker Compose
- Google Service Account JSON file with Viewer access to your domains in Search Console

## Initial Setup (Local Development)

1. **Clone the repository and prepare the environment file:**
   ```bash
   cp .env.example .env
   # Make sure DB and QUEUE variables match your preferences.
   ```

2. **Place your Google Service Account JSON:**
   Copy your Google service account file to `storage/app/google-service-account.json`. Ensure the path matches the `GOOGLE_SERVICE_ACCOUNT_JSON` variable in your `.env`.

3. **Build and start the Docker containers:**
   ```bash
   docker-compose up -d --build
   ```

4. **Install Composer dependencies:**
   ```bash
   docker-compose exec app composer install
   ```

5. **Generate an Application Key:**
   ```bash
   docker-compose exec app php artisan key:generate
   ```

6. **Run Migrations and Seeders:**
   ```bash
   docker-compose exec app php artisan migrate --seed
   ```
   *(This will create the default admin user and seed the target domains: Altaudit and Aiagentivo)*

7. **Compile Frontend Assets:**
   ```bash
   docker-compose exec app npm install
   docker-compose exec app npm run build
   ```

8. **Access the App:**
   Open your browser to `http://localhost:8000`. Log in with the default admin credentials (defined in `database/seeders/DatabaseSeeder.php`).

---

## Daily Operations

The system automatically runs a scheduled sync every day at 08:00 (Asia/Tashkent). However, for the very first time, you should backfill historical data.

### Backfilling Historical Data
Run this command to fetch the last 90 days of data:
```bash
docker-compose exec app php artisan seo:backfill --days=90
```

### Manual Synchronization
To trigger a manual sync for the last `SEO_SYNC_LOOKBACK_DAYS` (default 3):
```bash
docker-compose exec app php artisan seo:sync-gsc
```

To trigger a manual sync for a specific date (Format: YYYY-MM-DD):
```bash
docker-compose exec app php artisan seo:sync-gsc-date 2023-10-01
```

To manually rebuild the daily summaries for reporting:
```bash
docker-compose exec app php artisan seo:rebuild-summaries
```

---

## Deployment on RunCloud / Linux Hosting

This application is standard Laravel 12 and can be easily deployed via RunCloud without Docker, or using Docker natively on your VM.

### If deploying via RunCloud Native (PHP/Nginx/MySQL):
1. Connect your server to RunCloud.
2. Create a Web Application using the RunCloud panel (PHP 8.3).
3. Connect your Git repository to RunCloud and set the root path to `/public`.
4. Ensure your server's `.env` configuration has `DB_CONNECTION=mysql` and the database credentials reflect the RunCloud-created database.
5. Create a daemon in RunCloud for the Queue Worker:
   - Command: `php artisan queue:work --tries=3`
6. Create a Cron Job in RunCloud for the Scheduler:
   - Command: `php /path/to/app/artisan schedule:run >> /dev/null 2>&1`
7. Ensure storage permissions are correct (RunCloud handles this, but `storage/` and `bootstrap/cache/` must be writable by the `RunCloud` web user).
8. Upload your `google-service-account.json` securely to the server (e.g., in `storage/app/`) and ensure the path is correctly referenced in your `.env`.

### Updating the App
```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci
npm run build
php artisan optimize:clear
```