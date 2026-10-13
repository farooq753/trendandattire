<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tracking_token', 80)->unique();
            $table->string('checkout_token', 80)->unique();
            $table->char('currency_code', 3);
            $table->char('fx_base_code', 3)->nullable();
            $table->decimal('fx_rate', 18, 8)->nullable();
            $table->timestamp('fx_quoted_at')->nullable();
            $table->decimal('subtotal', 14, 4);
            $table->decimal('discount_total', 14, 4)->default(0);
            $table->decimal('shipping_total', 14, 4)->default(0);
            $table->decimal('tax_total', 14, 4)->default(0);
            $table->decimal('cod_fee', 14, 4)->default(0);
            $table->decimal('grand_total', 14, 4);
            $table->string('coupon_code')->nullable();
            $table->string('payment_method', 32);
            $table->string('payment_status', 32)->index();
            $table->string('fulfillment_status', 32)->index();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 32);
            $table->text('notes')->nullable();
            $table->string('shipping_method_name');
            $table->json('shipping_snapshot')->nullable();
            $table->json('tax_snapshot')->nullable();
            $table->timestamp('placed_at')->index();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'placed_at']);
        });

        Schema::create('order_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('type', 16);
            $table->string('name');
            $table->string('phone', 32)->nullable();
            $table->char('country_code', 2);
            $table->string('region')->nullable();
            $table->string('city');
            $table->string('line1');
            $table->string('line2')->nullable();
            $table->string('postal_code')->nullable();
            $table->timestamps();
            $table->unique(['order_id', 'type']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('sku');
            $table->string('fulfillment_type', 16);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 14, 4);
            $table->decimal('discount_total', 14, 4)->default(0);
            $table->decimal('tax_total', 14, 4)->default(0);
            $table->decimal('line_total', 14, 4);
            $table->string('production_status', 32)->nullable();
            $table->json('snapshot');
            $table->timestamps();
        });

        Schema::create('order_item_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->string('option_key', 64);
            $table->string('label');
            $table->string('value');
            $table->decimal('price_delta', 14, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('order_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->string('field_key', 64);
            $table->string('label');
            $table->decimal('original_value', 8, 2);
            $table->string('original_unit', 2);
            $table->decimal('normalized_cm', 8, 2);
            $table->timestamps();
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->timestamps();
        });

        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cart_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('status', 16)->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('method', 32);
            $table->string('status', 32)->index();
            $table->decimal('amount', 14, 4);
            $table->char('currency_code', 3);
            $table->string('provider')->nullable();
            $table->string('provider_reference')->nullable()->index();
            $table->string('bank_reference')->nullable();
            $table->string('evidence_path')->nullable();
            $table->text('customer_note')->nullable();
            $table->text('staff_note')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('provider_event_id')->nullable();
            $table->string('type');
            $table->string('status_before', 32)->nullable();
            $table->string('status_after', 32)->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->unique(['payment_id', 'provider_event_id']);
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 14, 4);
            $table->char('currency_code', 3);
            $table->string('status', 32)->index();
            $table->string('reason')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('method_name');
            $table->string('carrier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->string('status', 32)->default('label_created');
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });

        Schema::create('production_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32)->index();
            $table->unsignedSmallInteger('lead_time_days')->default(0);
            $table->date('due_on')->nullable();
            $table->text('staff_notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 32)->index();
            $table->string('reason');
            $table->text('details')->nullable();
            $table->string('resolution', 32)->nullable();
            $table->text('staff_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });

        Schema::create('order_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('type', 64);
            $table->string('message');
            $table->json('meta')->nullable();
            $table->boolean('is_public')->default(true);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_events');
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('returns');
        Schema::dropIfExists('production_jobs');
        Schema::dropIfExists('shipment_items');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('stock_reservations');
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('order_measurements');
        Schema::dropIfExists('order_item_configurations');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('order_addresses');
        Schema::dropIfExists('orders');
    }
};
