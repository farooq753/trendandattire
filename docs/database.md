# Database

The app database and the test database are separate MariaDB or MySQL schemas. Do not point `migrate:fresh` at a production database.

Money columns are `DECIMAL(14,4)`. Exchange rates are `DECIMAL(18,8)`. PHP never uses floats for those calculations.

## Groups of tables

- Access: `users`, `roles`, `permissions`, `permission_role`, `role_user`
- Catalog: `categories`, `products`, `product_images`, `product_attributes`, `attribute_values`, `product_variants`, `variant_attribute_values`
- Custom: `product_fabrics`, `product_customization_options`, `customization_option_values`, `product_measurement_schemas`
- Commerce: `carts`, `cart_items`, `wishlists`, `wishlist_items`, `coupons`, `coupon_usages`
- Inventory: `inventory_movements`, `stock_reservations`
- Geography: `countries`, `currencies`, `exchange_rates`, `shipping_zones`, `shipping_zone_locations`, `shipping_methods`, `tax_rules`
- Orders: `orders`, `order_addresses`, `order_items`, `order_item_configurations`, `order_measurements`, `payments`, `payment_events`, `refunds`, `shipments`, `shipment_items`, `production_jobs`, `returns`, `return_items`, `order_events`
- Operations: `customer_addresses`, `product_reviews`, `banners`, `store_settings`, `email_logs`, `audit_logs`, `contact_messages`

`store_settings.value` is JSON shaped as `{"v": ...}` so a string, list, or number can share one column.

## Commands

```sh
php artisan migrate
php artisan db:seed
php artisan inventory:release-expired-reservations
php artisan carts:prune-abandoned
```

Seeders refuse to create the demo admin when `APP_ENV=production`.
