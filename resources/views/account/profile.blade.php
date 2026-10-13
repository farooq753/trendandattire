@extends('layouts.store')
@section('title', 'Profile')
@section('content')
<h1>Profile</h1>
<form method="POST" action="{{ route('account.profile.update') }}" class="col-lg-6">
    @csrf @method('PUT')
    @include('components.form-field', ['name' => 'name', 'label' => 'Name', 'value' => $user->name])
    @include('components.form-field', ['name' => 'phone', 'label' => 'Phone', 'value' => $user->phone])
    <div class="mb-3"><label class="form-label" for="locale">Locale</label><select class="form-select" id="locale" name="locale"><option value="en" selected>English</option></select></div>
    @include('components.form-field', ['name' => 'timezone', 'label' => 'Timezone', 'value' => $user->timezone])
    <div class="mb-3"><label class="form-label" for="currency_code">Currency</label><select class="form-select" id="currency_code" name="currency_code">@foreach ($storeCurrencies as $currency)<option value="{{ $currency->code }}" @selected($user->currency_code === $currency->code)>{{ $currency->code }}</option>@endforeach</select></div>
    <div class="mb-3"><label class="form-label" for="profile_country">Country</label><select class="form-select" id="profile_country" name="country_code"><option value="">None</option>@foreach ($storeCountries as $country)<option value="{{ $country->code }}" @selected($user->country_code === $country->code)>{{ $country->name }}</option>@endforeach</select></div>
    <div class="mb-3"><label class="form-label" for="measurement_unit">Measurement unit</label><select class="form-select" id="measurement_unit" name="measurement_unit"><option value="cm" @selected($user->measurement_unit === 'cm')>cm</option><option value="in" @selected($user->measurement_unit === 'in')>in</option></select></div>
    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="measurement_history_consent" name="measurement_history_consent" value="1" @checked($user->measurement_history_consent_at)><label class="form-check-label" for="measurement_history_consent">Let this account show measurement history from my orders.</label></div>
    <x-button>Save profile</x-button>
</form>
<form class="mt-4" method="POST" action="{{ route('account.deletion') }}" data-confirm="Request account deletion?">@csrf<x-button variant="outline-danger">Request deletion</x-button></form>
@endsection
