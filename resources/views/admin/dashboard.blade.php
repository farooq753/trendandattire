@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<h1>Dashboard</h1>
<form class="row g-2 mb-4" method="GET">
    <div class="col-auto"><label class="form-label" for="from">From</label><input class="form-control" id="from" type="date" name="from" value="{{ $from }}"></div>
    <div class="col-auto"><label class="form-label" for="to">To</label><input class="form-control" id="to" type="date" name="to" value="{{ $to }}"></div>
    <div class="col-auto align-self-end"><button class="btn btn-navy" type="submit">Apply</button></div>
</form>
<div class="row g-3">
    <div class="col-md-4"><article class="bg-white border p-3"><h2 class="h6">Orders</h2><p class="display-6">{{ $orderCount }}</p></article></div>
    <div class="col-md-4"><article class="bg-white border p-3"><h2 class="h6">Paid total</h2><p class="display-6">{{ $paidTotal }}</p></article></div>
    <div class="col-md-4"><article class="bg-white border p-3"><h2 class="h6">Unpaid</h2><p class="display-6">{{ $pendingPayments }}</p></article></div>
    <div class="col-md-4"><article class="bg-white border p-3"><h2 class="h6">New customers</h2><p class="display-6">{{ $newCustomers }}</p></article></div>
    <div class="col-md-4"><article class="bg-white border p-3"><h2 class="h6">Low stock variants</h2><p class="display-6">{{ $lowStock }}</p></article></div>
    <div class="col-md-4"><article class="bg-white border p-3"><h2 class="h6">Production queue</h2><p class="display-6">{{ $productionQueue }}</p></article></div>
</div>
@endsection
