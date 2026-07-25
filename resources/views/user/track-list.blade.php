@extends('layouts.panel')

@section('title', 'My Filings')

@section('content')
<div class="itr-panel-hero">
    <div>
        <h1>My Filings</h1>
        <p>Track Self &amp; Expert returns for FY {{ $app['financial_year'] }} — open the next step anytime.</p>
    </div>
    <div class="itr-gap-row">
        <a class="itr-btn itr-btn-primary" href="{{ route('user.choose-service', ['mode' => 'self']) }}">{!! icon('spark') !!} Self Filing</a>
        <a class="itr-btn itr-btn-orange" href="{{ route('user.choose-service', ['mode' => 'assisted']) }}">{!! icon('users') !!} Hire Tax Expert</a>
    </div>
</div>

@if($filings->isEmpty())
<div class="itr-card"><div class="itr-card-b">
    <div class="itr-empty-state">
        {!! iconBox('plus') !!}
        <h3>No filings yet</h3>
        <p>Start with Form 16 self-filing, or Hire a Tax Expert for capital gains and complex income.</p>
        <a class="itr-btn itr-btn-orange" href="{{ route('user.choose-service') }}">Start Filing</a>
    </div>
</div></div>
@else
<div class="itr-filing-cards">
@foreach($filings as $f)
@php $pct = filingProgressPercent($f); @endphp
<article class="itr-filing-card">
    <div class="itr-filing-card-top">
        <div>
            <span class="itr-tag {{ $f->filing_mode === 'self' ? 'itr-tag-orange' : '' }}">{{ $f->filing_mode === 'self' ? 'Self Filing' : ($f->plan->name ?? 'Tax Expert') }}</span>
            <h3>Filing #{{ $f->id }} · {{ $f->itr_type }}</h3>
            <div class="itr-help">AY {{ $f->assessment_year }} · Started {{ formatDate($f->created_at) }}</div>
        </div>
        <div class="itr-filing-card-status">{!! statusBadge($f->status) !!}</div>
    </div>
    <div class="itr-progress-rail"><span style="width: {{ $pct }}%"></span></div>
    <div class="itr-filing-card-meta">
        <span>{{ $pct }}% complete</span>
        <span>{{ $f->acknowledgement_no ? 'ACK '.$f->acknowledgement_no : 'Next: '.nextStepLabel($f) }}</span>
    </div>
    @include('partials.filing-actions', ['filing' => $f])
</article>
@endforeach
</div>
@include('partials.pager', ['paginator' => $filings])
@endif
@endsection
