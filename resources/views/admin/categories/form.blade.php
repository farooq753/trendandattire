@extends('layouts.admin')
@section('content')
<h1>{{ $category->exists ? 'Edit category' : 'New category' }}</h1>
<form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
    @csrf @if($category->exists) @method('PUT') @endif
    <label class="form-label" for="name">Name</label><input class="form-control mb-2" id="name" name="name" value="{{ old('name', $category->name) }}" required>
    <label class="form-label" for="nav_group">Nav group</label><input class="form-control mb-2" id="nav_group" name="nav_group" value="{{ old('nav_group', $category->nav_group) }}">
    <label class="form-label" for="parent_id">Parent</label>
    <select class="form-select mb-2" id="parent_id" name="parent_id"><option value="">None</option>@foreach ($parents as $parent)<option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>@endforeach</select>
    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))><label class="form-check-label" for="is_active">Active</label></div>
    <x-button>Save</x-button>
</form>
@endsection
