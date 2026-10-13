@extends('layouts.admin')
@section('content')
<h1>Store settings</h1>
<form method="POST" action="{{ route('admin.settings.update') }}">
@csrf @method('PUT')
@foreach (['store_name' => 'Store name', 'support_email' => 'Support email', 'cod_fee' => 'COD fee', 'cod_max_amount' => 'COD maximum', 'bank_account_name' => 'Bank account name', 'bank_name' => 'Bank name', 'bank_account_number' => 'Account number', 'bank_swift' => 'SWIFT', 'reservation_minutes' => 'Reservation minutes', 'cancellation_window_hours' => 'Cancellation window hours', 'return_window_days' => 'Return window days'] as $key => $label)
<label class="form-label" for="{{ $key }}">{{ $label }}</label>
<input class="form-control mb-2" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, is_array($values[$key] ?? null) ? implode(',', $values[$key]) : ($values[$key] ?? '')) }}">
@endforeach
<label class="form-label" for="cod_countries">COD countries (comma separated)</label>
<input class="form-control mb-2" id="cod_countries" name="cod_countries" value="{{ old('cod_countries', is_array($values['cod_countries'] ?? null) ? implode(',', $values['cod_countries']) : '') }}">
<label class="form-label" for="bank_instructions">Bank instructions</label>
<textarea class="form-control mb-2" id="bank_instructions" name="bank_instructions">{{ old('bank_instructions', $values['bank_instructions'] ?? '') }}</textarea>
<div class="form-check"><input class="form-check-input" type="checkbox" name="cod_enabled" id="cod_enabled" value="1" @checked(old('cod_enabled', $values['cod_enabled'] ?? false))><label class="form-check-label" for="cod_enabled">COD enabled</label></div>
<div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="require_email_verification" id="require_email_verification" value="1" @checked(old('require_email_verification', $values['require_email_verification'] ?? false))><label class="form-check-label" for="require_email_verification">Require email verification</label></div>
<x-button>Save settings</x-button>
</form>
@endsection
