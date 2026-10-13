@extends('layouts.admin')
@section('content')
<h1>Banners</h1>
<a class="btn btn-navy" href="{{ route('admin.banners.create') }}">New banner</a>
@foreach ($banners as $banner)<p><a href="{{ route('admin.banners.edit', $banner) }}">{{ $banner->title }}</a></p>@endforeach
@endsection
