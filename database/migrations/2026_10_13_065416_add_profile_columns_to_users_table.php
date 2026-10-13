<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 32)->nullable()->after('email');
            $table->string('locale', 12)->default('en')->after('remember_token');
            $table->string('timezone', 64)->default('UTC');
            $table->string('currency_code', 3)->default('USD');
            $table->string('country_code', 2)->nullable();
            $table->string('measurement_unit', 2)->default('cm');
            $table->timestamp('measurement_history_consent_at')->nullable();
            $table->timestamp('deletion_requested_at')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'locale',
                'timezone',
                'currency_code',
                'country_code',
                'measurement_unit',
                'measurement_history_consent_at',
                'deletion_requested_at',
                'is_active',
            ]);
        });
    }
};
