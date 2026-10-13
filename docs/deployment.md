# Deployment

This application is staging-capable as a Laravel app. It is not production-ready until the items below are true for the target host.

## Required before a public launch

- `APP_ENV=production`, `APP_DEBUG=false`, and a unique `APP_KEY`
- HTTPS termination and `SESSION_SECURE_COOKIE=true`
- A private MariaDB or MySQL database, not the local sample user
- `php artisan migrate --force` against that database, never `migrate:fresh`
- `npm run build` and a persistent `storage` and `bootstrap/cache` directory
- A queue worker and a scheduler (see the matching docs)
- Real SMTP, or an explicit decision to keep mail on `log`
- Real bank details in store settings
- Stripe or PayPal credentials only if those methods should be enabled
- Backups and a restore drill
- No demo users. `db:seed` skips demo accounts in production, and you should still not run the catalog seeder against a live catalog unless you intend to.

## Process

```sh
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

Run the queue worker and scheduler as supervised processes. The web user must be able to write `storage` and `bootstrap/cache` and must not be able to read `.env` through the web server.

Private uploads (logos, payment evidence) stay on the `private` disk under `storage/app/private`. Do not move that disk into `public`.
