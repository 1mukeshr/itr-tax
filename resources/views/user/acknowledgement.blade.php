@extends('layouts.panel')

@section('title', 'Acknowledgement')

@section('content')
@php
    $prefill = json_decode($filing->notes ?: '{}', true) ?: [];
    $tax = ($filing->tax_regime ?? 'new') === 'old' ? $filing->tax_old_regime : $filing->tax_new_regime;
    $isSelf = $filing->filing_mode === 'self';
    $everified = ($filing->everify_status ?? 'pending') === 'verified';
@endphp

<div class="itr-ack-page">
    <div class="itr-page-title itr-ack-page-title">
        <div>
            <h1>Acknowledgement</h1>
            <p>
                Filing #{{ $filing->id }}
                · {!! statusBadge($filing->status) !!}
                · {{ $isSelf ? 'Self Filing' : 'Tax Expert Assisted' }}
            </p>
        </div>
    </div>
    @include('partials.steps', ['filing' => $filing])

@if($filing->acknowledgement_no)
    <section class="itr-ack-receipt" aria-labelledby="ack-title">
        <header class="itr-ack-receipt-head">
            <div class="itr-ack-brand">{!! brandLogo('full') !!}</div>
            <span class="itr-ack-stamp">
                {!! icon('check-circle') !!}
                {{ $isSelf ? 'Prepared' : 'Filed' }}
                @if($everified)
                    · E-verified
                @endif
            </span>
        </header>

        <div class="itr-ack-hero">
            <p class="itr-ack-kicker">
                AY {{ $filing->assessment_year }} · {{ $filing->itr_type }}
                · {{ $isSelf ? 'Your filing reference' : 'Filing acknowledgement' }}
            </p>
            <h2 id="ack-title" class="itr-ack-no">{{ $filing->acknowledgement_no }}</h2>
            @if($isSelf)
                <p class="itr-ack-note">ITR Tax reference only — complete e-filing &amp; e-verify on the IT portal.</p>
            @endif
        </div>

        <dl class="itr-ack-grid">
            <div>
                <dt>PAN</dt>
                <dd>{{ $filing->pan ?? '—' }}</dd>
            </div>
            <div>
                <dt>Filed on</dt>
                <dd>{{ formatDate($filing->filed_at, 'd M Y, h:i A') }}</dd>
            </div>
            <div>
                <dt>Tax regime</dt>
                <dd>{{ strtoupper($filing->tax_regime ?? 'new') }}</dd>
            </div>
            <div>
                <dt>Gross income</dt>
                <dd>{{ money($filing->gross_salary) }}</dd>
            </div>
            <div>
                <dt>Estimated tax</dt>
                <dd>{{ money($tax) }}</dd>
            </div>
            <div>
                <dt>Employer</dt>
                <dd>{{ $prefill['employer_name'] ?? '—' }}</dd>
            </div>
        </dl>

        <div class="itr-ack-everify {{ $everified ? 'is-ok' : '' }}">
            <div class="itr-ack-everify-head">
                <h3>E-verify</h3>
                <span class="itr-ack-side-chip {{ $everified ? 'is-ok' : '' }}">
                    {{ $everified ? 'Verified' : 'Pending · 30 days' }}
                </span>
            </div>
            @unless($everified)
                <p class="itr-ack-side-lead">Return is incomplete on the IT portal until you e-verify.</p>
                <ol class="itr-everify-steps itr-everify-steps-inline">
                    <li><span>1</span><div><strong>Login</strong><em>IT portal with PAN</em></div></li>
                    <li><span>2</span><div><strong>e-Verify</strong><em>e-File → Returns</em></div></li>
                    <li><span>3</span><div><strong>Method</strong><em>Aadhaar OTP / net banking / DSC</em></div></li>
                    <li><span>4</span><div><strong>Ref</strong><em><code>{{ $filing->acknowledgement_no }}</code></em></div></li>
                </ol>
                <form method="post" action="{{ route('user.mark-everify', $filing) }}" class="itr-ack-side-form">
                    @csrf
                    <button class="itr-btn itr-btn-primary itr-btn-sm" type="submit">
                        {!! icon('check') !!} I’ve e-verified
                    </button>
                </form>
            @else
                <div class="itr-ack-verified">
                    {!! icon('check-circle') !!}
                    <div>
                        <strong>E-verify complete</strong>
                        <span>
                            @if($filing->everified_at)
                                {{ formatDate($filing->everified_at, 'd M Y') }}
                            @else
                                Recorded on ITR Tax
                            @endif
                        </span>
                    </div>
                </div>
            @endunless
        </div>

        <div class="itr-ack-actions">
            @if($receipt)
                <a class="itr-btn itr-btn-primary itr-btn-sm" href="{{ route('user.download-receipt', $filing) }}">
                    {!! icon('download') !!} Download ACK
                </a>
            @endif
            <a class="itr-btn itr-btn-outline itr-btn-sm" href="{{ route('user.track', $filing) }}">View timeline</a>
            <a class="itr-btn itr-btn-orange itr-btn-sm" href="{{ route('user.choose-service') }}">
                {!! icon('spark') !!} File another
            </a>
        </div>
    </section>
@else
    <div class="itr-ack-empty">
        <h3>ITR not filed yet</h3>
        <p>
            {{ $isSelf
                ? 'Finish Confirm & File to generate your reference.'
                : 'Your expert will file and share the ACK here.' }}
        </p>
        <a class="itr-btn itr-btn-primary itr-btn-sm" href="{{ filingContinueUrl($filing) }}">Continue filing</a>
    </div>
@endif
</div>
@endsection
