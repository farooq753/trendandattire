@extends('layouts.store')
@section('title', 'Size guide | Trend & Attire')
@section('content')
<h1>Size guide</h1>
<p>Standard sizes run from XS to 3XL. The values below are the sizes currently configured for the catalog. They are a guide for ready-made garments.</p>
@if ($sizes->isEmpty())
    <p>XS, S, M, L, XL, 2XL, and 3XL are available when a product offers standard sizing.</p>
@else
    <ul>@foreach ($sizes as $size)<li>{{ $size->value }}</li>@endforeach</ul>
@endif
@endsection
