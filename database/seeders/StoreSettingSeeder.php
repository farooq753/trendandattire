<?php

namespace Database\Seeders;

use App\Services\StoreSettings;
use Illuminate\Database\Seeder;

class StoreSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = app(StoreSettings::class);
        $settings->put('cod_enabled', true);
        $settings->put('cod_countries', ['US']);
        $settings->put('cod_fee', '2.0000');
        $settings->put('cod_max_amount', '500.0000');
        $settings->put('reservation_minutes', 1440);
        $settings->put('cancellation_window_hours', 12);
        $settings->put('return_window_days', 30);
        $settings->put('require_email_verification', false);
        $settings->put('support_email', 'hello@example.com');
        $settings->put('bank_account_name', 'Configure the account name in admin');
        $settings->put('bank_name', 'Configure the bank name in admin');
        $settings->put('bank_account_number', 'Configure the account number in admin');
        $settings->put('bank_swift', '');
        $settings->put('bank_instructions', 'Bank transfer stays unpaid until a staff member verifies the transfer. A customer reference is not proof of payment.');
        $settings->put('order_sequence', 1000);
    }
}
