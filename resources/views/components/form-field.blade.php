@php
    $type = $type ?? 'text';
    $value = $value ?? null;
@endphp
<div class="mb-3">
    <label class="form-label" for="{{ $name }}">{{ $label }}</label>
    <input class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}">
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
