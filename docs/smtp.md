# SMTP

Local and test environments use `MAIL_MAILER=log` or the array mailer. That is not production mail.

To send real mail, set `MAIL_MAILER=smtp` and provide `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME`, and `MAIL_FROM_ADDRESS`. Keep those values in the server environment, not in git.

Messages the app sends:

- Order received
- Order and production status changes
- Contact form copy to the store
- Password reset, through Laravel's password broker, with a generic response that does not reveal whether the email exists

Each queued message also writes `email_logs` with the mailable class, recipient, and status. The log does not store the body or secrets. A queued status means the job was dispatched, not that the mailbox accepted it.
