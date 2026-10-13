<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_fabrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('composition')->nullable();
            $table->decimal('price_delta', 14, 4)->default(0);
            $table->unsignedSmallInteger('extra_lead_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_customization_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('code', 64);
            $table->string('name');
            $table->unsignedTinyInteger('step');
            $table->string('input_type', 16);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'code']);
        });

        Schema::create('customization_option_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_customization_option_id');
            $table->foreign('product_customization_option_id', 'cov_option_id_foreign')
                ->references('id')
                ->on('product_customization_options')
                ->cascadeOnDelete();
            $table->string('label');
            $table->string('value');
            $table->decimal('price_delta', 14, 4)->default(0);
            $table->unsignedSmallInteger('extra_lead_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_measurement_schemas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('field_key', 64);
            $table->string('label');
            $table->decimal('min_cm', 8, 2);
            $table->decimal('max_cm', 8, 2);
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'field_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_measurement_schemas');
        Schema::dropIfExists('customization_option_values');
        Schema::dropIfExists('product_customization_options');
        Schema::dropIfExists('product_fabrics');
    }
};
