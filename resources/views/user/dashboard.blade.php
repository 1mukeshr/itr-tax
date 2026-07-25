@extends('layouts.panel')

@section('title', 'Dashboard')

@section('content')
@php
    $pct = !empty($continueFiling) ? filingProgressPercent($continueFiling) : 0;
@endphp

<section class="itr-udash" aria-label="Customer dashboard">
    <div class="itr-udash-metrics" aria-label="Filing summary">
        <div class="itr-udash-metric">
            <span class="itr-udash-metric-ico" aria-hidden="true">{!! icon('file') !!}</span>
            <div>
                <span>Total filings</span>
                <strong>{{ (int) $stats['total'] }}</strong>
            </div>
        </div>
        <div class="itr-udash-metric itr-udash-metric-hot">
            <span class="itr-udash-metric-ico" aria-hidden="true">{!! icon('clock') !!}</span>
            <div>
                <span>In progress</span>
                <strong>{{ (int) $stats['active'] }}</strong>
            </div>
        </div>
        <div class="itr-udash-metric itr-udash-metric-ok">
            <span class="itr-udash-metric-ico" aria-hidden="true">{!! icon('check-circle') !!}</span>
            <div>
                <span>Completed</span>
                <strong>{{ (int) $stats['filed'] }}</strong>
            </div>
        </div>
    </div>

    @if(!empty($continueFiling))
    <article class="itr-card itr-udash-continue">
        <div class="itr-card-h">
            <span class="itr-udash-pill">Continue filing</span>
            {!! statusBadge($continueFiling->status) !!}
            <span class="itr-udash-continue-meta">
                #{{ $continueFiling->id }}
                · {{ $continueFiling->filing_mode === 'self' ? 'Self' : 'Tax Expert' }}
                · {{ $continueFiling->itr_type }}
            </span>
        </div>
        <div class="itr-card-b">
            <div class="itr-udash-continue-row">
                <div>
                    <h2>Next: {{ nextStepLabel($continueFiling) }}</h2>
                    <div class="itr-udash-progress" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="itr-udash-progress-track"><span style="width: {{ $pct }}%"></span></div>
                        <strong>{{ $pct }}%</strong>
                    </div>
                </div>
                <div class="itr-udash-continue-aside">
                    <a class="itr-btn itr-btn-primary" href="{{ filingContinueUrl($continueFiling) }}">
                        Continue {!! icon('arrow-right') !!}
                    </a>
                    <a class="itr-btn itr-btn-outline itr-btn-sm" href="{{ route('user.documents', $continueFiling) }}">Documents</a>
                    <a class="itr-btn itr-btn-outline itr-btn-sm" href="{{ route('user.track', $continueFiling) }}">Timeline</a>
                </div>
            </div>
            <div class="itr-udash-steps">
                @include('partials.steps', ['filing' => $continueFiling])
            </div>
        </div>
    </article>
    @elseif($filings->isEmpty())
    <div class="itr-udash-start">
        <a class="itr-udash-start-card" href="{{ route('user.choose-service', ['mode' => 'self']) }}">
            <span class="itr-udash-start-ico">{!! iconBox('spark') !!}</span>
            <h3>Self Filing</h3>
            <p>Questions → Form 16 → summary → confirm. Free for simple returns.</p>
            <span class="itr-link-more">Start now {!! icon('arrow-right') !!}</span>
        </a>
        <a class="itr-udash-start-card" href="{{ route('user.choose-service', ['mode' => 'assisted']) }}">
            <span class="itr-udash-start-ico">{!! iconBox('users') !!}</span>
            <h3>Hire a Tax Expert</h3>
            <p>Pay once → expert reviews → you approve → get ACK.</p>
            <span class="itr-link-more">Choose plan {!! icon('arrow-right') !!}</span>
        </a>
        <a class="itr-udash-start-card" href="{{ url('/tax-calculator') }}">
            <span class="itr-udash-start-ico">{!! iconBox('chart') !!}</span>
            <h3>Tax Calculator</h3>
            <p>Compare old vs new regime before you begin.</p>
            <span class="itr-link-more">Open tool {!! icon('arrow-right') !!}</span>
        </a>
    </div>
    @endif

    <section class="itr-card itr-udash-panel">
        <div class="itr-card-h">
            <span>Your filings</span>
            <a href="{{ route('user.track-list') }}">View all</a>
        </div>
        <div class="itr-card-b itr-udash-panel-b">
            @if($filings->isEmpty())
                <div class="itr-udash-empty">
                    {!! iconBox('file') !!}
                    <h3>No filings yet</h3>
                    <p>Begin with Self Filing for Form 16, or Hire a Tax Expert for complex income.</p>
                    <a class="itr-btn itr-btn-orange itr-btn-sm" href="{{ route('user.choose-service') }}">{!! icon('spark') !!} Start Filing</a>
                </div>
            @else
                <ul class="itr-udash-filings">
                    @foreach($filings as $f)
                        @php $fp = filingProgressPercent($f); @endphp
                        <li>
                            <a class="itr-udash-filing" href="{{ filingContinueUrl($f) }}">
                                <div class="itr-udash-filing-id">
                                    <strong>#{{ $f->id }}</strong>
                                    <span>{{ $f->filing_mode === 'self' ? 'Self' : ($f->plan->name ?? 'Tax Expert') }} · {{ $f->itr_type }}</span>
                                </div>
                                <div class="itr-udash-filing-meta">
                                    {!! statusBadge($f->status) !!}
                                    <div class="itr-udash-mini-rail" aria-hidden="true"><span style="width: {{ $fp }}%"></span></div>
                                </div>
                                <div class="itr-udash-filing-cta">
                                    <em>{{ nextStepLabel($f) }}</em>
                                    {!! icon('arrow-right') !!}
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>

    <div class="itr-card itr-udash-tip-card">
        <div class="itr-card-b itr-udash-tip">
            {!! icon('shield') !!}
            <p>Keep Form 16 ready, compare regimes on Tax Summary, and e-verify within 30 days on the Income Tax portal after ACK.</p>
        </div>
    </div>
</section>
@endsection
