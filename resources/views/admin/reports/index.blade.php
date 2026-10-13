@extends('layouts.admin')
@section('content')
<h1>Reports</h1>
<form method="GET" class="row g-2 mb-3">
<div class="col-auto"><label class="form-label" for="from">From</label><input class="form-control" id="from" type="date" name="from" value="{{ $from }}"></div>
<div class="col-auto"><label class="form-label" for="to">To</label><input class="form-control" id="to" type="date" name="to" value="{{ $to }}"></div>
<div class="col-auto align-self-end"><button class="btn btn-navy" type="submit">Run</button></div>
</form>
<p>Orders {{ $orders }}. Paid total {{ $paid }}. Low-stock variants {{ $lowStock }}.</p>
@if (auth()->user()->hasPermission('reports.export'))
<p><a href="{{ route('admin.reports.export', ['type' => 'orders', 'from' => $from, 'to' => $to]) }}">Orders CSV</a> · <a href="{{ route('admin.reports.export', ['type' => 'inventory', 'from' => $from, 'to' => $to]) }}">Inventory CSV</a></p>
@endif
@endsection
