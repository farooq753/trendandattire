@extends('layouts.admin')
@section('content')
<h1>Production</h1>
@foreach ($jobs as $job)
<article class="bg-white border p-3 mb-2">
<p>{{ $job->orderItem->order->number }} · {{ $job->orderItem->name }} · @include('components.status-badge', ['status' => $job->status])</p>
<form method="POST" action="{{ route('admin.production.update', $job) }}">
@csrf
<label class="form-label" for="status-{{ $job->id }}">Next status</label>
<select class="form-select mb-2" id="status-{{ $job->id }}" name="status">
@foreach (\App\Enums\ProductionStatus::from($job->status)->allowedNext() as $status)
<option value="{{ $status }}">{{ str_replace('_', ' ', $status) }}</option>
@endforeach
</select>
<label class="form-label" for="notes-{{ $job->id }}">Staff note</label>
<textarea class="form-control mb-2" id="notes-{{ $job->id }}" name="staff_notes"></textarea>
<button class="btn btn-navy" type="submit">Update</button>
</form>
</article>
@endforeach
{{ $jobs->links() }}
@endsection
