@php
    $fromCode = $from ?? 'USD';
    $toCode = session('currency_code', $fromCode);
    $service = app(\App\Services\CurrencyService::class);
    $approximate = false;
    try {
        $converted = $service->convert((string) $amount, $fromCode, $toCode);
        $text = $service->format($converted['amount'], $toCode);
        $approximate = $fromCode !== $toCode;
    } catch (\Throwable) {
        $text = $service->format((string) $amount, $fromCode);
    }
@endphp
<span>{{ $text }}</span>
@if ($approximate)
    <span class="visually-hidden">converted with a saved store rate</span>
@endif
