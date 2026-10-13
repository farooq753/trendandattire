# Backup and restore

Back up the MariaDB or MySQL database and the `storage/app/private` directory together. Private files are payment evidence and embroidery logos. Public catalog images live on the `public` disk (`storage/app/public`).

Example database dump:

```sh
mysqldump --single-transaction --routines trendandattire > trendandattire.sql
```

Restore into an empty database, then point `.env` at it and run `php artisan migrate --force` so any newer migrations apply. Restore `storage/app/private` to the same relative paths recorded in `payments.evidence_path` and personalization logo paths.

Test a restore on a non-production host before relying on it. Do not restore a production dump over the local sample database without meaning to.
