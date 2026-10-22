@extends('layouts.store')
@section('title', 'FAQ | Trend & Attire')
@section('content')
<h1>FAQ</h1>
<div class="accordion" id="faq">
    <div class="accordion-item"><h2 class="accordion-header" id="q2"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#a2">Will I be charged duties?</button></h2><div id="a2" class="accordion-collapse collapse show" data-bs-parent="#faq"><div class="accordion-body">Duties are not collected at checkout unless a duties integration is configured. None is configured. The recipient may pay duties on delivery.</div></div></div>
    <div class="accordion-item"><h2 class="accordion-header" id="q3"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a3">Are prices converted live?</button></h2><div id="a3" class="accordion-collapse collapse" data-bs-parent="#faq"><div class="accordion-body">No. Optional currencies use a rate saved by the store, with the rate and timestamp stored on the order.</div></div></div>
</div>
@endsection
