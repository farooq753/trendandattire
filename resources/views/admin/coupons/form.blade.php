@extends('layouts.admin')
@section('content')
<h1>Coupon</h1>
<form method="POST" action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}">
@csrf @if($coupon->exists) @method('PUT') @endif
<label class="form-label" for="code">Code</label><input class="form-control mb-2" id="code" name="code" value="{{ old('code', $coupon->code) }}" required>
<label class="form-label" for="type">Type</label><select class="form-select mb-2" id="type" name="type"><option value="percent">Percent</option><option value="fixed" @selected(old('type', $coupon->type) === 'fixed')>Fixed</option></select>
<label class="form-label" for="value">Value</label><input class="form-control mb-2" id="value" name="value" value="{{ old('value', $coupon->value) }}" required>
<div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $coupon->is_active))><label class="form-check-label" for="is_active">Active</label></div>
<x-button>Save</x-button>
</form>
@endsection
