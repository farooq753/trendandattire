<x-mail::message>
# Order {{ $order->number }}

We received your order. Payment status: {{ str_replace('_', ' ', $order->payment_status) }}.

@if ($order->payment_method === 'bank_transfer')
Bank transfer stays unpaid until the store verifies it.
@endif

@if ($order->payment_method === 'cod')
Cash on delivery stays unpaid until collection is confirmed.
@endif

Thanks,<br>
Trend & Attire
</x-mail::message>
