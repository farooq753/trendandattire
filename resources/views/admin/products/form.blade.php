@extends('layouts.admin')
@section('title', $product->exists ? 'Edit product' : 'New product')
@section('content')
<h1>{{ $product->exists ? 'Edit '.$product->name : 'New product' }}</h1>
<form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
    @csrf @if($product->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="name">Name</label><input class="form-control" id="name" name="name" value="{{ old('name', $product->name) }}" required></div>
        <div class="col-md-3"><label class="form-label" for="sku">SKU</label><input class="form-control" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required></div>
        <div class="col-md-3"><label class="form-label" for="slug">Slug</label><input class="form-control" id="slug" name="slug" value="{{ old('slug', $product->slug) }}"></div>
        <div class="col-md-4"><label class="form-label" for="gender">Gender</label><select class="form-select" id="gender" name="gender">@foreach (['men','women','unisex'] as $gender)<option value="{{ $gender }}" @selected(old('gender', $product->gender) === $gender)>{{ $gender }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="garment_type">Type</label><select class="form-select" id="garment_type" name="garment_type">@foreach (['top','pants','set','jacket','accessory'] as $type)<option value="{{ $type }}" @selected(old('garment_type', $product->garment_type) === $type)>{{ $type }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="fulfillment_type">Fulfillment</label><select class="form-select" id="fulfillment_type" name="fulfillment_type">@foreach (['ready','custom','both'] as $type)<option value="{{ $type }}" @selected(old('fulfillment_type', $product->fulfillment_type) === $type)>{{ $type }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="regular_price">Regular price</label><input class="form-control" id="regular_price" name="regular_price" value="{{ old('regular_price', $product->regular_price) }}" required></div>
        <div class="col-md-4"><label class="form-label" for="sale_price">Sale price</label><input class="form-control" id="sale_price" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}"></div>
        <div class="col-md-4"><label class="form-label" for="cost_price">Cost (admin only)</label><input class="form-control" id="cost_price" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}"></div>
        <div class="col-md-4"><label class="form-label" for="currency_code">Currency</label><input class="form-control" id="currency_code" name="currency_code" value="{{ old('currency_code', $product->currency_code ?: 'USD') }}" required></div>
        <div class="col-12"><label class="form-label" for="short_description">Short description</label><input class="form-control" id="short_description" name="short_description" value="{{ old('short_description', $product->short_description) }}"></div>
        <div class="col-12"><label class="form-label" for="description">Description</label><textarea class="form-control" id="description" name="description">{{ old('description', $product->description) }}</textarea></div>
        <div class="col-md-6"><label class="form-label" for="fabric_composition">Fabric composition</label><input class="form-control" id="fabric_composition" name="fabric_composition" value="{{ old('fabric_composition', $product->fabric_composition) }}"></div>
        <div class="col-md-3"><label class="form-label" for="gsm">GSM</label><input class="form-control" id="gsm" name="gsm" value="{{ old('gsm', $product->gsm) }}"></div>
        <div class="col-md-3"><label class="form-label" for="production_lead_days">Lead days</label><input class="form-control" id="production_lead_days" name="production_lead_days" value="{{ old('production_lead_days', $product->production_lead_days) }}"></div>
        <div class="col-md-4"><label class="form-label" for="origin_country">Origin (only if known)</label><input class="form-control" id="origin_country" name="origin_country" maxlength="2" value="{{ old('origin_country', $product->origin_country) }}"></div>
        <div class="col-md-4"><label class="form-label" for="weight_grams">Weight grams</label><input class="form-control" id="weight_grams" name="weight_grams" value="{{ old('weight_grams', $product->weight_grams) }}"></div>
        <div class="col-md-4"><label class="form-label" for="low_stock_threshold">Low stock</label><input class="form-control" id="low_stock_threshold" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}"></div>
    </div>
    <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" @checked(old('is_published', $product->is_published))><label class="form-check-label" for="is_published">Published</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1" @checked(old('is_featured', $product->is_featured))><label class="form-check-label" for="is_featured">Featured</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="track_inventory" id="track_inventory" value="1" @checked(old('track_inventory', $product->track_inventory ?? true))><label class="form-check-label" for="track_inventory">Track ready-made inventory</label></div>
    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="allows_personalization" id="allows_personalization" value="1" @checked(old('allows_personalization', $product->allows_personalization))><label class="form-check-label" for="allows_personalization">Allow embroidery or logo</label></div>
    <x-button>Save product</x-button>
</form>
@if ($product->exists)
    <hr>
    <h2 class="h4">Variant</h2>
    <form method="POST" action="{{ route('admin.products.variants.store', ['product' => $product->id]) }}">
        @csrf
        <label class="form-label" for="variant_sku">SKU</label><input class="form-control mb-2" id="variant_sku" name="sku" required>
        <label class="form-label" for="stock_on_hand">Stock</label><input class="form-control mb-2" id="stock_on_hand" name="stock_on_hand" type="number" min="0" value="0" required>
        @foreach (($attributeValues ?? []) as $group => $values)
            <fieldset class="mb-2"><legend class="h6">{{ $group }}</legend>
                @foreach ($values as $value)
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="attribute_value_ids[]" value="{{ $value->id }}" id="av-{{ $value->id }}"><label class="form-check-label" for="av-{{ $value->id }}">{{ $value->value }}</label></div>
                @endforeach
            </fieldset>
        @endforeach
        <button class="btn btn-outline-secondary" type="submit">Add variant</button>
    </form>
    <ul>@foreach ($product->variants as $variant)<li>{{ $variant->sku }} — {{ $variant->label() }} — on hand {{ $variant->stock_on_hand }}, reserved {{ $variant->reserved }}</li>@endforeach</ul>
    <form method="POST" action="{{ route('admin.products.images.store', ['product' => $product->id]) }}" enctype="multipart/form-data">
        @csrf
        <label class="form-label" for="image">Catalog image</label>
        <input class="form-control mb-2" id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp" required>
        <button class="btn btn-outline-secondary" type="submit">Upload</button>
    </form>
@endif
@endsection
