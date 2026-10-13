# Queues

`.env.example` uses the database queue. Order and contact mail implement `ShouldQueue`. `OrderNotifier` queues mail after the database commit and writes an `email_logs` row without the message body.

Run a worker:

```sh
php artisan queue:work --tries=3 --timeout=90
```

Failed jobs land in `failed_jobs`. Inspect them with `php artisan queue:failed`. A failed mail job should be retried or logged. It must not be treated as proof that the customer received the message.

Tests set `QUEUE_CONNECTION=sync` so assertions do not depend on a worker. Production SMTP is a separate setup step. Until `MAIL_MAILER` is a real transport, messages stay on the log driver.
