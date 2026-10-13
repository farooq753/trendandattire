# Trend & Attire

International storefront for ready-made and custom-stitched scrub suits. The application is Laravel 13 with server-rendered Blade, Bootstrap 5, and MariaDB. It is built to be exercised locally and in staging. It is not production-ready until live provider accounts are connected and the deployment checklist in `docs/` is completed for a real host.

## Stack

- PHP 8.3+ (developed on PHP 8.5)
- Laravel 13.35
- MariaDB 10.11 or MySQL 8
- Node 22 and Vite for Bootstrap 5, Bootstrap Icons, and local fonts
- PHPUnit 12 against a separate database

Money is stored as `DECIMAL` and calculated with `brick/math`. Prices are never computed with PHP floats. Catalog prices are in the product currency. Checkout converts with a saved `exchange_rates` row and snapshots the rate on the order. There is no live foreign-exchange feed.

## Local setup (Linux)

```sh
composer install
cp .env.example .env
php artisan key:generate
# Create the MariaDB database and user named in .env.example, then:
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

The seeded password `local_dev_only` and the demo accounts below are for local development only. Change them outside this machine.

Demo accounts, created only when `APP_ENV` is not `production`:

- Super admin: `admin@trendandattire.test` / `password`
- Customer: `customer@trendandattire.test` / `password`

Create another admin after roles are seeded:

```sh
php artisan store:create-admin you@example.com
```

The command prints a password once. It does not write that password into the repository.

Windows with XAMPP is documented in [docs/xampp-local-setup.md](docs/xampp-local-setup.md).

## Tests

`phpunit.xml` points at database `trendandattire_test`. Create that database with the same user as the app database, then:

```sh
php artisan test
```

## What is intentionally not live

Stripe, PayPal, DHL, FedEx, UPS rate APIs, a foreign-exchange feed, production SMTP, and country tax-authority integrations are boundaries. Cash on delivery and bank transfer have real state machines. Card and PayPal checkout cannot place an order until credentials exist, and a webhook is accepted only after signature verification and a server-side status read. See [docs/payments.md](docs/payments.md) and [docs/shipping.md](docs/shipping.md).

## Docs

- [Architecture](docs/architecture.md)
- [Database](docs/database.md)
- [XAMPP / Windows](docs/xampp-local-setup.md)
- [Deployment](docs/deployment.md)
- [HTTPS](docs/https.md)
- [Queues](docs/queues.md)
- [Scheduler](docs/scheduler.md)
- [SMTP](docs/smtp.md)
- [Payments](docs/payments.md)
- [Shipping](docs/shipping.md)
- [Backup and restore](docs/backup-restore.md)
- [Troubleshooting](docs/troubleshooting.md)
