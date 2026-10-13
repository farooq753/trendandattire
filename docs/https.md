# HTTPS

Terminate TLS at the reverse proxy or web server. Redirect HTTP to HTTPS. Set `APP_URL` to the `https://` origin and set `SESSION_SECURE_COOKIE=true` so the session cookie is not sent on plain HTTP.

Trust the proxy headers only for your own proxy (`trusted proxies` in the Laravel bootstrap if the app sits behind one). The `SecurityHeaders` middleware sends `X-Content-Type-Options`, `X-Frame-Options`, a referrer policy, a permissions policy, and a content security policy that allows inline styles used by Bootstrap.

Do not serve the private disk. Payment evidence and measurement logos are downloaded only through authenticated application code, not through a public URL.
