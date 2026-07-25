@extends('layouts.panel')

@section('title', 'Track Filing')

@section('content')
@php
    $pct = filingProgressPercent($filing);
    $statusMsg = match ($filing->status) {
        'questionnaire_pending', 'draft' => 'Answer a few simple questions to continue.',
        'documents_pending' => 'Upload Form 16 (required) and other proofs, then continue.',
        'docs_requested' => 'Expert needs more documents — upload them to continue.',
        'details_review' => 'Confirm your details, then proceed to checkout.',
        'summary_pending' => 'Enter income and TDS from Form 16, then compare regimes.',
        'payment_pending' => 'Complete checkout to get a tax expert assigned.',
        'paid' => 'Payment received. Waiting for a tax expert to be assigned.',
        'assigned' => 'Tax expert assigned. Waiting for review to start.',
        'under_review' => 'Tax expert is reviewing your case.',
        'customer_summary' => 'Review and approve your tax summary so the expert can file.',
        'customer_approved' => 'You approved. Tax expert will file your return next.',
        'ready_to_file' => 'Confirm declaration to generate your filing reference.',
        'filed' => 'Return marked filed. Open ACK when ready.',
        'completed' => 'Filing completed. Download ACK/reference and e-verify within 30 days on the Income Tax portal.',
        default => 'Next: '.nextStepLabel($filing),
    };
@endphp
<div class="itr-page-title">
    <h1>Track Filing #{{ $filing->id }}</h1>
    <p>{!! statusBadge($filing->status) !!} · {{ $filing->filing_mode === 'self' ? 'Self Filing' : 'Tax Expert Assisted' }} · {{ $filing->itr_type }}</p>
    @if($filing->ca_id)
        <p class="itr-mt-sm"><a class="itr-btn itr-btn-outline itr-btn-sm" href="{{ route('chat.open-filing', $filing) }}">{!! icon('message') !!} Chat with tax expert</a></p>
    @endif
</div>
@include('partials.steps', ['filing' => $filing])

<div class="itr-panel-hero">
    <div>
        <h1>{{ $pct }}% complete</h1>
        <p>{{ $statusMsg }}</p>
        <div class="itr-progress-rail"><span style="width: {{ $pct }}%"></span></div>
    </div>
    <a class="itr-btn itr-btn-primary" href="{{ filingContinueUrl($filing) }}">{{ nextStepLabel($filing) }} {!! icon('arrow-right') !!}</a>
</div>

<div class="itr-card itr-mb-md"><div class="itr-card-b">
    @include('partials.filing-actions', ['filing' => $filing])
    @if(! in_array($filing->status, ['filed', 'completed', 'cancelled'], true))
        <div class="itr-gap-row itr-mt-md">
            @if($filing->filing_mode === 'self' && ($upgradePlans ?? collect())->isNotEmpty())
                <details class="itr-upgrade-box">
                    <summary class="itr-btn itr-btn-orange itr-btn-sm">Upgrade to Expert</summary>
                    <form method="post" action="{{ route('user.upgrade-assisted', $filing) }}" class="itr-mt-sm">
                        @csrf
                        <div class="itr-form-group">
                            <label>Choose Tax Expert plan</label>
                            <select class="itr-form-control" name="plan_id" required>
                                @foreach($upgradePlans as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} — {{ money($p->price) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="itr-btn itr-btn-primary itr-btn-sm" type="submit">Convert Self → Expert</button>
                    </form>
                </details>
            @endif
            @if(empty($hasPaid))
                <form method="post" action="{{ route('user.cancel-filing', $filing) }}" onsubmit="return confirm('Cancel this filing?');">
                    @csrf
                    <button class="itr-btn itr-btn-outline itr-btn-sm" type="submit">Cancel filing</button>
                </form>
            @endif
        </div>
    @endif
</div></div>

<div class="itr-grid-2">
<div class="itr-card"><div class="itr-card-h">Timeline</div><div class="itr-card-b">
<div class="itr-timeline">
@if($logs->isEmpty())
    <div class="itr-empty">No updates yet.</div>
@endif
@foreach($logs as $log)
<div class="itr-tl">
    <strong>{{ statusLabel($log->new_status) }}</strong>
    <div class="itr-help">{{ $log->remark ?? '' }} · {{ formatDate($log->created_at, 'd M Y, h:i A') }}</div>
</div>
@endforeach
</div>
</div></div>
<div>
<div class="itr-card"><div class="itr-card-b">
    <h3>{{ $filing->filing_mode === 'self' ? 'Self filing' : 'Assigned Expert' }}</h3>
    @if($filing->filing_mode === 'assisted')
        @if($ca)
            <p class="itr-text-ink"><strong>{{ $ca->name }}</strong></p>
            <div class="itr-help">{{ $ca->email }} · target review after your approval</div>
        @elseif($filing->status === 'paid')
            <p>Payment done. A tax expert will be assigned soon.</p>
        @else
            <p>Expert is assigned after payment.</p>
        @endif
    @else
        <p>You are preparing this return yourself. E-verify on the Income Tax portal after you have the official ACK.</p>
    @endif
</div></div>
<div class="itr-card"><div class="itr-card-h">Notes from expert</div><div class="itr-card-b">
@if($notes->isEmpty())
    <div class="itr-empty">No notes yet.</div>
@endif
@foreach($notes as $n)
    <p class="itr-text-ink">{{ $n->note }}</p>
    <div class="itr-help">{{ $n->author->name ?? '' }}</div>
@endforeach
</div></div>
</div>
</div>
@endsection
