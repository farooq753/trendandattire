@extends('layouts.admin')
@section('title', 'Products')
@section('content')
<h1>Products</h1>
<a class="btn btn-navy mb-3" href="{{ route('admin.products.create') }}">New product</a>
<table class="table bg-white"><thead><tr><th>Name</th><th>SKU</th><th>Published</th></tr></thead><tbody>
@foreach ($products as $product)
<tr><td><a href="{{ route('admin.products.edit', $product) }}">{{ $product->name }}</a></td><td>{{ $product->sku }}</td><td>{{ $product->is_published ? 'Yes' : 'No' }}</td></tr>
@endforeach
</tbody></table>
{{ $products->links() }}
@endsection
