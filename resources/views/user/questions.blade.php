@extends('layouts.panel')

@section('title', 'Questions')

@section('content')
@php $total = count($questions); @endphp
<div class="itr-page-title">
    <h1>Quick questions</h1>
    <p>Filing #{{ $filing->id }} · {{ $filing->itr_type }} · {{ $total }} answers · about 2 minutes</p>
</div>
@include('partials.steps', ['filing' => $filing])

<form method="post" action="{{ route('user.save-questions', $filing) }}" class="itr-card itr-order-card">
@csrf
<div class="itr-card-b">
<div class="itr-questions-grid">
@foreach($questions as $key => $q)
    @php $n = $loop->iteration; @endphp
    <div class="itr-form-group itr-q-item">
        <label><span class="itr-q-num">{{ $n }}</span> {{ $q['label'] }}</label>
        <select class="itr-form-control" name="q_{{ $key }}" required>
            <option value="">Select…</option>
            @foreach(($q['options'] ?? []) as $val => $label)
                <option value="{{ $val }}" {{ ($answers[$key] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
@endforeach
</div>
<div class="itr-order-bar">
    <div class="itr-order-bar-copy">
        <strong>Next: upload documents</strong>
        <span>Form 16 is required to continue after this.</span>
    </div>
    <button class="itr-btn itr-btn-orange itr-btn-lg" type="submit">{!! icon('arrow-right') !!} Continue</button>
</div>
</div>
</form>
@endsection
