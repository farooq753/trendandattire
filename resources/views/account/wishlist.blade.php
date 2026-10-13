@extends('layouts.store')
@section('title', 'Wishlist')
@section('content')
<h1>Wishlist</h1>
@forelse ($items as $item)
    <article class="d-flex justify-content-between bg-white border p-3 mb-2">
        <a href="{{ route('products.show', $item->product) }}">{{ $item->product->name }}</a>
        <form method="POST" action="{{ route('wishlist.destroy', $item) }}">@csrf @method('DELETE')<button class="btn btn-link" type="submit">Remove</button></form>
    </article>
@empty
    <p>Nothing saved yet.</p>
@endforelse
@endsection
