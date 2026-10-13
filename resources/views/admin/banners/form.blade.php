@extends('layouts.admin')
@section('content')
<h1>Banner</h1>
<form method="POST" action="{{ $banner->exists ? route('admin.banners.update', $banner) : route('admin.banners.store') }}" enctype="multipart/form-data">
@csrf @if($banner->exists) @method('PUT') @endif
<label class="form-label" for="title">Title</label><input class="form-control mb-2" id="title" name="title" value="{{ old('title', $banner->title) }}" required>
<label class="form-label" for="subtitle">Subtitle</label><input class="form-control mb-2" id="subtitle" name="subtitle" value="{{ old('subtitle', $banner->subtitle) }}">
<label class="form-label" for="link_url">Link</label><input class="form-control mb-2" id="link_url" name="link_url" value="{{ old('link_url', $banner->link_url) }}">
<input type="hidden" name="placement" value="home">
<div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $banner->is_active ?? true))><label class="form-check-label" for="is_active">Active</label></div>
<x-button>Save</x-button>
</form>
@endsection
