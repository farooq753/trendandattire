@extends('layouts.admin')
@section('content')
<h1>Returns</h1>
@foreach ($returns as $return)
<article class="bg-white border p-3 mb-2">
<p><a href="{{ route('admin.orders.show', $return->order) }}">{{ $return->order->number }}</a> @include('components.status-badge', ['status' => $return->status]) {{ $return->reason }}</p>
<form method="POST" action="{{ route('admin.returns.update', $return) }}">@csrf
<label class="form-label" for="return-status-{{ $return->id }}">Status</label>
<select class="form-select mb-2" id="return-status-{{ $return->id }}" name="status">
@foreach (['approved','rejected','received','refunded','closed'] as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach
</select>
<div class="form-check"><input class="form-check-input" type="checkbox" name="restock" id="restock-{{ $return->id }}" value="1"><label class="form-check-label" for="restock-{{ $return->id }}">Restock ready-made units when received</label></div>
<button class="btn btn-navy" type="submit">Update return</button>
</form>
</article>
@endforeach
@endsection
