@extends('layouts.store')
@section('title', 'Configure '.$product->name)
@section('content')
<h1>Configure {{ $product->name }}</h1>
<ol>
    <li>Product</li><li>Fabric</li><li>Color and style</li><li>Sizing</li><li>Measurements</li><li>Personalization</li><li>Price and lead time</li><li>Review</li><li>Confirm</li>
</ol>
@if ($review)
    <section class="bg-white border p-3 mb-4">
        <h2 class="h5">Review</h2>
        <p>Unit price @include('components.price', ['amount' => $review['unit_price'], 'from' => $product->currency_code])</p>
        <p>Production lead time {{ $review['lead_days'] }} days, before shipping.</p>
        @foreach ($review['configuration'] as $row)
            <p class="mb-0">{{ $row['label'] }}: {{ $row['value'] }} ({{ $row['price_delta'] }})</p>
        @endforeach
        @foreach ($review['measurements'] as $measurement)
            <p class="mb-0">{{ $measurement['label'] }}: {{ $measurement['original_value'] }} {{ $measurement['original_unit'] }} ({{ $measurement['normalized_cm'] }} cm)</p>
        @endforeach
    </section>
@endif
<form method="POST" action="{{ route('custom.store', $product) }}" enctype="multipart/form-data">
    @csrf
    <fieldset class="mb-4">
        <legend>Fabric</legend>
        @foreach ($product->fabrics->where('is_active', true) as $fabric)
            <div class="form-check">
                <input class="form-check-input" type="radio" name="fabric_id" id="fabric-{{ $fabric->id }}" value="{{ $fabric->id }}" @checked(old('fabric_id', $input['fabric_id'] ?? null) == $fabric->id)>
                <label class="form-check-label" for="fabric-{{ $fabric->id }}">{{ $fabric->name }} @if($fabric->composition) — {{ $fabric->composition }} @endif (+{{ $fabric->price_delta }})</label>
            </div>
        @endforeach
    </fieldset>
    @foreach ($product->customizationOptions->where('is_active', true)->where('input_type', 'select') as $option)
        <div class="mb-3">
            <label class="form-label" for="opt-{{ $option->code }}">{{ $option->name }} @if($option->is_required) (required) @endif</label>
            <select class="form-select" id="opt-{{ $option->code }}" name="options[{{ $option->code }}]">
                <option value="">Choose</option>
                @foreach ($option->values->where('is_active', true) as $value)
                    <option value="{{ $value->id }}" @selected(old('options.'.$option->code, $input['options'][$option->code] ?? null) == $value->id)>{{ $value->label }} (+{{ $value->price_delta }})</option>
                @endforeach
            </select>
        </div>
    @endforeach
    <fieldset class="mb-3">
        <legend>Sizing method</legend>
        <div class="form-check"><input class="form-check-input" type="radio" name="sizing_method" id="size-standard" value="standard" @checked(old('sizing_method', $input['sizing_method'] ?? 'standard') === 'standard')><label class="form-check-label" for="size-standard">Standard size</label></div>
        <div class="form-check"><input class="form-check-input" type="radio" name="sizing_method" id="size-custom" value="custom" @checked(old('sizing_method', $input['sizing_method'] ?? '') === 'custom')><label class="form-check-label" for="size-custom">Custom measurements</label></div>
    </fieldset>
    <label class="form-label" for="standard_size">Standard size</label>
    <select class="form-select mb-3" id="standard_size" name="standard_size">
        @foreach (['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'] as $size)
            <option value="{{ $size }}" @selected(old('standard_size', $input['standard_size'] ?? '') === $size)>{{ $size }}</option>
        @endforeach
    </select>
    @foreach ($product->measurementSchemas as $field)
        <div class="row g-2 mb-2">
            <div class="col-8">
                <label class="form-label" for="m-{{ $field->field_key }}">{{ $field->label }} @if($field->is_required) (required for custom) @endif</label>
                <input class="form-control" id="m-{{ $field->field_key }}" name="measurements[{{ $field->field_key }}][value]" value="{{ old('measurements.'.$field->field_key.'.value') }}">
            </div>
            <div class="col-4">
                <label class="form-label" for="u-{{ $field->field_key }}">Unit</label>
                <select class="form-select" id="u-{{ $field->field_key }}" name="measurements[{{ $field->field_key }}][unit]">
                    <option value="cm">cm</option>
                    <option value="in">in</option>
                </select>
            </div>
        </div>
    @endforeach
    <fieldset class="mb-3">
        <legend>Personalization</legend>
        <div class="form-check"><input class="form-check-input" type="radio" name="personalization_type" id="pers-none" value="none" checked><label class="form-check-label" for="pers-none">None</label></div>
        <div class="form-check"><input class="form-check-input" type="radio" name="personalization_type" id="pers-text" value="text"><label class="form-check-label" for="pers-text">Embroidery text</label></div>
        <div class="form-check"><input class="form-check-input" type="radio" name="personalization_type" id="pers-logo" value="logo"><label class="form-check-label" for="pers-logo">Logo upload</label></div>
        <label class="form-label" for="personalization_text">Text</label>
        <input class="form-control" id="personalization_text" name="personalization_text" maxlength="20">
        <label class="form-label" for="logo">Logo</label>
        <input class="form-control" id="logo" name="logo" type="file" accept=".jpg,.jpeg,.png,.webp">
    </fieldset>
    <label class="form-label" for="quantity">Quantity</label>
    <input class="form-control mb-3" id="quantity" name="quantity" type="number" min="1" max="10" value="1">
    <button class="btn btn-outline-secondary" name="action" value="review" type="submit">Review price</button>
    <button class="btn btn-navy" name="action" value="confirm" type="submit">Add to cart</button>
</form>
@endsection
