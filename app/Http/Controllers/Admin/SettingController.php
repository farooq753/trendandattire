<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\StoreSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(StoreSettings $settings): View
    {
        $keys = [
            'store_name', 'support_email', 'cod_enabled', 'cod_fee', 'cod_max_amount',
            'cod_countries', 'bank_account_name', 'bank_name', 'bank_account_number', 'bank_swift',
            'bank_instructions', 'reservation_minutes', 'require_email_verification',
            'cancellation_window_hours', 'return_window_days',
        ];
        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $settings->get($key);
        }

        return view('admin.settings.edit', ['values' => $values]);
    }

    public function update(Request $request, StoreSettings $settings, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'store_name' => ['required', 'string', 'max:120'],
            'support_email' => ['required', 'email'],
            'cod_enabled' => ['nullable', 'boolean'],
            'cod_fee' => ['required', 'numeric', 'min:0'],
            'cod_max_amount' => ['required', 'numeric', 'min:0'],
            'cod_countries' => ['nullable', 'string', 'max:255'],
            'bank_account_name' => ['nullable', 'string', 'max:120'],
            'bank_name' => ['nullable', 'string', 'max:120'],
            'bank_account_number' => ['nullable', 'string', 'max:64'],
            'bank_swift' => ['nullable', 'string', 'max:20'],
            'bank_instructions' => ['nullable', 'string', 'max:1000'],
            'reservation_minutes' => ['required', 'integer', 'min:15', 'max:10080'],
            'require_email_verification' => ['nullable', 'boolean'],
            'cancellation_window_hours' => ['required', 'integer', 'min:0', 'max:168'],
            'return_window_days' => ['required', 'integer', 'min:0', 'max:365'],
        ]);

        $data['cod_enabled'] = $request->boolean('cod_enabled');
        $data['require_email_verification'] = $request->boolean('require_email_verification');
        $data['cod_countries'] = collect(explode(',', (string) $data['cod_countries']))
            ->map(fn ($code) => strtoupper(trim($code)))
            ->filter()
            ->values()
            ->all();

        foreach ($data as $key => $value) {
            $settings->put($key, $value);
        }

        $audit->log('settings.updated', null, null, ['keys' => array_keys($data)]);

        return back()->with('status', 'Store settings saved.');
    }
}
