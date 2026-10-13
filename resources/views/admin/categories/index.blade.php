@extends('layouts.admin')
@section('content')
<h1>Categories</h1>
<a class="btn btn-navy mb-3" href="{{ route('admin.categories.create') }}">New category</a>
<ul>@foreach ($categories as $category)<li><a href="{{ route('admin.categories.edit', $category) }}">{{ $category->name }}</a></li>@endforeach</ul>
{{ $categories->links() }}
@endsection
