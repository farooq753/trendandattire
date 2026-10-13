# Payments

Cash on delivery and bank transfer are implemented. Stripe and PayPal are disabled until credentials exist. Checkout rejects `stripe` and `paypal` so the store cannot record a fake card charge.

## Cash on delivery

COD is offered only when `cod_enabled` is true, the shipping method allows it, the country is in `cod_countries` when that list is set, and the merchandise plus shipping is within `cod_max_amount`. A configured fee is added. The payment stays `pending` until a staff member with `payments.verify` confirms collection. Customer checkout does not mark COD paid.

## Bank transfer

The confirmation page shows the bank details stored in settings. The customer may submit a reference and a private evidence file. That moves the payment to `evidence_submitted`. It does not mark it paid. A finance user verifies or rejects it. Verification consumes the stock reservation and then sets `paid`. If the reservation cannot be consumed, the payment stays unpaid.

## Stripe and PayPal

Set `STRIPE_SECRET` and `STRIPE_WEBHOOK_SECRET`, or `PAYPAL_CLIENT_ID`, `PAYPAL_SECRET`, and `PAYPAL_WEBHOOK_ID`. Leave them empty to keep the webhooks returning HTTP 503.

`POST /webhooks/stripe` checks the `Stripe-Signature` header (five-minute tolerance) and then retrieves the PaymentIntent from Stripe. `POST /webhooks/paypal` asks PayPal to verify the transmission and then reads the capture. The webhook JSON alone is not treated as payment. Provider event ids are stored on `payment_events` so a replay does not apply twice.

The store never accepts a card number or CVV. Turning checkout on for these providers still requires a hosted or provider-controlled payment step, which is not connected in this repository.

Refunds recorded in admin are ledger entries with permission checks. They do not call Stripe or PayPal.
