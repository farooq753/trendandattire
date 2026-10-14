# XAMPP on Windows

Development and automated tests for this repository run on Linux. Use these steps when running the same application on a Windows machine with XAMPP.

1. Install XAMPP with PHP 8.3 or newer and MariaDB or MySQL. Install Composer and Node.js 22.
2. Start Apache and MySQL from the XAMPP control panel.
3. Open phpMyAdmin and create two databases: `trendandattire` and `trendandattire_test`.
4. Create a user, for example `ta_app`, with access only to those two databases. Do not reuse the sample password from `.env.example` on a shared machine.
5. Copy the project into a folder such as `C:\xampp\htdocs\trendandattire`. Prefer `php artisan serve` over exposing the repository root through Apache. Opening `http://localhost/trendandattire/public/` is supported: image and stylesheet URLs include that folder. `APP_URL` can stay `http://localhost`.
6. In a terminal:

```powershell
cd C:\xampp\htdocs\trendandattire
copy .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

7. Set `DB_CONNECTION=mariadb` (or `mysql`), `DB_HOST=127.0.0.1`, `DB_PORT=3306`, and the database name you created.
8. Visit `http://127.0.0.1:8000`. Sign in as the local admin only on this machine, then change the password.
9. For tests, point `phpunit.xml` at `trendandattire_test` and run `php artisan test`.

Queue and scheduler still need a running worker and `php artisan schedule:work` or Windows Task Scheduler. See `docs/queues.md` and `docs/scheduler.md`.
