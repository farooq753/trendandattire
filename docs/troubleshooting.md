# Troubleshooting

## The home page is unstyled

Run `npm install` and `npm run build`. Blade loads the Vite manifest. Tests call `withoutVite()` and do not need the build.

## Database connection refused

Confirm MariaDB or MySQL is running and that `DB_*` matches a user who can use `trendandattire`. On this Linux environment, systemd may not start MariaDB; `mysqld_safe` can. Windows users should start MySQL from the XAMPP panel.

## Migrations fail on a long foreign key name

MariaDB limits identifiers to 64 characters. The customization option value foreign key is named `cov_option_id_foreign` for that reason.

## Checkout says a currency is unavailable

Add an `exchange_rates` row from the default currency to that currency, with `source` of `manual` and `effective_at` in the past. The store does not fetch live rates.

## A bank transfer was submitted but the order is unpaid

That is the intended state. Staff with `payments.verify` must verify it. Customer evidence never marks a payment paid.

## Stripe or PayPal webhook returns 503

The matching environment secrets are empty. Fill them only when the provider account exists. A 400 means the signature or the provider status check failed. The order stays unpaid.

## Queue mail never arrives

`MAIL_MAILER=log` writes to the log. A database queue also needs `php artisan queue:work`. Check `failed_jobs` and `email_logs`.

## Demo admin does not exist

Seeding skips demo users when `APP_ENV=production`. Run `php artisan store:create-admin you@example.com` after `RolePermissionSeeder`.
