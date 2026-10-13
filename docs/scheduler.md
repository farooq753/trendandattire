# Scheduler

`routes/console.php` schedules:

- `inventory:release-expired-reservations` every five minutes
- `carts:prune-abandoned` daily

Run one scheduler process:

```sh
php artisan schedule:work
```

On a server, the cron entry is:

```cron
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

Expired bank-transfer reservations return reserved units to available stock. They do not mark an order paid. Abandoned guest carts older than 30 days are deleted. Authenticated carts are kept.
