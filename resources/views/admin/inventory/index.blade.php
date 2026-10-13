@extends('layouts.admin')
@section('content')
<h1>Inventory</h1>
<p><a href="{{ route('admin.inventory.index', ['low' => 1]) }}">Low stock only</a></p>
<table class="table bg-white"><thead><tr><th>SKU</th><th>Available</th><th>Adjust</th></tr></thead><tbody>
@foreach ($variants as $variant)
<tr>
<td>{{ $variant->sku }}<br><span class="small">{{ $variant->product->name }} {{ $variant->label() }}</span></td>
<td>{{ $variant->availableQuantity() }} on hand {{ $variant->stock_on_hand }}, reserved {{ $variant->reserved }}</td>
<td>
<form method="POST" action="{{ route('admin.inventory.adjust', $variant) }}" class="d-flex gap-2">
@csrf
<label class="visually-hidden" for="delta-{{ $variant->id }}">Quantity change</label>
<input class="form-control" id="delta-{{ $variant->id }}" name="delta" type="number" required>
<button class="btn btn-sm btn-navy" type="submit">Save</button>
</form>
</td>
</tr>
@endforeach
</tbody></table>
{{ $variants->links() }}
@endsection
