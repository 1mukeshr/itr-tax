@extends('layouts.panel')

@section('title', 'Review & File')

@section('content')
@php
    $tax = ($filing->tax_regime ?? 'new') === 'old' ? $filing->tax_old_regime : $filing->tax_new_regime;
    $prefill = json_decode($filing->notes ?: '{}', true) ?: [];
    $tds = (float) ($prefill['tds_deducted'] ?? 0);
    $net = round((float) $tax - $tds, 2);
@endphp
<div class="itr-page-title">
    <h1>Confirm &amp; finish</h1>
    <p>One last check — then generate your ITR Tax filing reference.</p>
</div>
@include('partials.steps', ['filing' => $filing])

<div class="itr-grid-2">
<div class="itr-card"><div class="itr-card-h">{!! icon('file') !!} ITR preview</div><div class="itr-card-b">
<div class="itr-grid-2">
    <div><div class="itr-help">Mode</div><strong>Self Filing</strong></div>
    <div><div class="itr-help">Profile</div><strong>{{ ucwords(str_replace('_', ' ', $filing->income_profile ?? 'salaried')) }}</strong></div>
    <div><div class="itr-help">ITR Type</div><strong>{{ $filing->itr_type }}</strong></div>
    <div><div class="itr-help">PAN</div><strong>{{ $filing->pan ?? '-' }}</strong></div>
    <div><div class="itr-help">Employer</div><strong>{{ $prefill['employer_name'] ?? '-' }}</strong></div>
    <div><div class="itr-help">Gross income</div><strong>{{ money($filing->gross_salary) }}</strong></div>
    <div><div class="itr-help">Selected regime</div><strong>{{ strtoupper($filing->tax_regime ?? 'new') }}</strong></div>
    <div><div class="itr-help">Estimated tax</div><strong>{{ money($tax) }}</strong></div>
    <div><div class="itr-help">TDS deducted</div><strong>{{ money($tds) }}</strong></div>
    <div><div class="itr-help">Refund / (Payable)</div><strong>{{ money($net) }}</strong></div>
</div>
</div></div>

<div class="itr-card"><div class="itr-card-h">Before you file</div><div class="itr-card-b">
<ul class="itr-check-list">
    <li>{!! icon('check') !!} Income &amp; TDS match Form 16 / AIS</li>
    <li>{!! icon('check') !!} Correct tax regime selected</li>
    <li>{!! icon('check') !!} PAN and assessment year are correct</li>
    <li>{!! icon('check') !!} You will e-verify within 30 days after upload on the Income Tax portal</li>
</ul>
<div class="itr-alert itr-alert-info itr-mt-md">{!! icon('shield') !!} This step creates an ITR Tax filing reference. Complete official e-filing/e-verification on the Income Tax portal within 30 days (or as notified by CBDT).</div>
</div></div>
</div>

@if($filing->status === 'ready_to_file')
<form method="post" action="{{ route('user.self-file', $filing) }}" class="itr-mt-md">
    @csrf
    <label class="itr-declare">
        <input type="checkbox" name="declaration" value="1" required>
        <span>I declare that the information in this return is true and complete to the best of my knowledge.</span>
    </label>
    <div class="itr-actions-row">
        <button class="itr-btn itr-btn-orange" type="submit">{!! icon('check-circle') !!} Confirm &amp; Generate Reference</button>
        <a class="itr-btn itr-btn-outline" href="{{ route('user.summary', $filing) }}">Edit summary</a>
    </div>
</form>
@elseif(in_array($filing->status, ['filed', 'completed'], true))
    <div class="itr-alert itr-alert-success itr-mt-md">Already filed. {!! statusBadge($filing->status) !!}</div>
    <a class="itr-btn itr-btn-primary" href="{{ route('user.acknowledgement', $filing) }}">{!! icon('download') !!} View Acknowledgement</a>
@else
    <div class="itr-alert itr-alert-info itr-mt-md">Finish tax summary before you confirm.</div>
    <a class="itr-btn itr-btn-orange" href="{{ filingContinueUrl($filing) }}">{!! icon('arrow-right') !!} {{ nextStepLabel($filing) }}</a>
@endif
@endsection
