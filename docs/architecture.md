# Architecture

Trend & Attire is one Laravel application at the repository root. The storefront and admin are server-rendered Blade views. Bootstrap 5, Bootstrap Icons, and font files are bundled with Vite. There is no Vue, React, or SPA.

## Request flow

Web routes live in `routes/web.php`. `SetStorefrontContext` stores locale, currency, country, and a guest cart token. `SecurityHeaders` adds a baseline content security policy and frame policy. Staff routes use the `staff` middleware plus a `permission:{slug}` middleware. Super admins pass every permission check. Customers have no roles.

Policies authorize order access. Admin screens also check permissions, so hiding a button is not the access control.

## Purchase models

Ready-made lines reference a `product_variants` row. Available quantity is `stock_on_hand - reserved`. Checkout locks the variant. Cash on delivery deducts stock when the order is placed. Bank transfer reserves stock until staff verify the transfer or the reservation expires.

Custom lines do not use variant stock. `CustomConfigurator` recalculates fabric, style, measurements, embroidery, price, and lead time from catalog rows. Measurements keep the entered value and unit and also store a centimeter equivalent. Logo files go on the private disk.

## Checkout

`CheckoutService` recalculates every total inside a database transaction, writes an immutable order snapshot, and refuses a second insert with the same checkout token. Order numbers come from a locked `store_settings` sequence. Guest tracking uses a random token plus an email match, not a sequential id.

Payment status, fulfillment status, and production status are separate columns.

## Money and currency

`App\Support\Money` uses `Brick\Math` with half-up rounding. Orders store `currency_code`, `fx_base_code`, `fx_rate`, and `fx_quoted_at`. A missing saved rate blocks checkout in that currency. The store does not claim a live FX feed.

## Integration boundaries

`StripeGateway` and `PayPalGateway` are disabled until environment credentials exist. They verify the webhook, then read the payment from the provider. DHL, FedEx, and UPS are labels a person can type onto a manual shipment. They do not return live rates.
