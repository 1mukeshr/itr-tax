@extends('layouts.panel')

@section('title', 'Tax Summary')

@section('content')
@php
    $saving = (float) ($breakdown['saving'] ?? abs((float) $filing->tax_old_regime - (float) $filing->tax_new_regime));
    $better = $breakdown['recommended'] ?? (((float) $filing->tax_new_regime <= (float) $filing->tax_old_regime) ? 'new' : 'old');
    $employer = old('employer_name', $prefill['employer_name'] ?? '');
    $tds = old('tds_deducted', $prefill['tds_deducted'] ?? $breakdown['tds_deducted'] ?? 0);
@endphp
<div class="itr-page-title">
    <h1>Tax Summary</h1>
    <p>Enter figures from Form 16 / AIS. Compare old vs new regime, then continue.</p>
</div>
@include('partials.steps', ['filing' => $filing])

<form method="post" action="{{ route('user.save-summary', $filing) }}" id="summaryForm" data-live-summary>
@csrf
<div class="itr-grid-2">
<div class="itr-card"><div class="itr-card-h">Income details (from Form 16)</div><div class="itr-card-b">
    <div class="itr-form-group">
        <label>Employer name</label>
        <input class="itr-form-control" name="employer_name" value="{{ $employer }}" required>
    </div>
    <div class="itr-form-group">
        <label>Gross salary / total income (₹)</label>
        <input class="itr-form-control" type="number" step="1" min="1" name="gross_salary" id="sumGross" value="{{ old('gross_salary', $filing->gross_salary) }}" required>
    </div>
    <div class="itr-form-group">
        <label>Deductions 80C/80D etc. (₹) — old regime</label>
        <input class="itr-form-control" type="number" step="1" min="0" name="total_deductions" id="sumDeduct" value="{{ old('total_deductions', $filing->total_deductions ?? 0) }}" required>
    </div>
    <div class="itr-form-group">
        <label>Form 16 TDS (₹)</label>
        <input class="itr-form-control" type="number" step="1" min="0" name="tds_deducted" id="sumTds" value="{{ $tds }}" required>
    </div>
    <div class="itr-form-group">
        <label>AIS / 26AS TDS (₹)</label>
        <input class="itr-form-control" type="number" step="1" min="0" name="ais_tds" value="{{ old('ais_tds', $aisTds ?? $filing->ais_tds ?? 0) }}">
    </div>
    @if(!empty($aisCheck))
        <div class="itr-alert {{ $aisCheck['match'] ? 'itr-alert-success' : 'itr-alert-error' }}">{{ $aisCheck['message'] }}</div>
    @endif
    <div class="itr-soft-note">Enter exact values from Form 16 Part B and AIS. Do not estimate.</div>
</div></div>

<div class="itr-card"><div class="itr-card-h">Old vs New regime</div><div class="itr-card-b">
    <div class="itr-grid-2">
        <label class="itr-box itr-regime-pick {{ ($filing->tax_regime ?? '') === 'old' ? 'itr-hot' : '' }}">
            <input type="radio" name="tax_regime" value="old" {{ ($filing->tax_regime ?? '') === 'old' ? 'checked' : '' }}>
            <strong>Old Regime</strong>
            <div class="itr-price itr-price-md" id="sumOldTax">{{ money($filing->tax_old_regime) }}</div>
            <div class="itr-help">With deductions</div>
        </label>
        <label class="itr-box itr-regime-pick {{ ($filing->tax_regime ?? 'new') !== 'old' ? 'itr-hot' : '' }}">
            <input type="radio" name="tax_regime" value="new" {{ ($filing->tax_regime ?? 'new') !== 'old' ? 'checked' : '' }}>
            <strong>New Regime</strong>
            <div class="itr-price itr-price-md" id="sumNewTax">{{ money($filing->tax_new_regime) }}</div>
            <div class="itr-help">Lower slabs, fewer deductions</div>
        </label>
    </div>
    <div class="itr-alert itr-alert-success itr-mt-md" id="sumRec">
        Lower estimated tax: <strong>{{ strtoupper($better) }} regime</strong> (difference about {{ money($saving) }}). Simplified estimate including §87A where applicable — not a complete tax computation.
    </div>
</div></div>
</div>

<div class="itr-card itr-mt-md"><div class="itr-card-h">Income &amp; tax breakup</div>
<div class="itr-table-wrap"><table class="itr-compare-table">
<tr><th>Particulars</th><th>Old regime</th><th>New regime</th></tr>
<tr><td>Gross income</td><td id="brGrossOld">{{ money($breakdown['gross_salary'] ?? $filing->gross_salary) }}</td><td id="brGrossNew">{{ money($breakdown['gross_salary'] ?? $filing->gross_salary) }}</td></tr>
<tr><td>Standard deduction</td><td id="brStdOld">{{ money($breakdown['standard_deduction_old'] ?? 50000) }}</td><td id="brStdNew">{{ money($breakdown['standard_deduction_new'] ?? 75000) }}</td></tr>
<tr><td>Chapter VIA deductions</td><td id="brDedOld">{{ money($breakdown['total_deductions'] ?? $filing->total_deductions) }}</td><td>-</td></tr>
<tr><td>Taxable income</td><td id="brTaxableOld">{{ money($breakdown['taxable_old'] ?? 0) }}</td><td id="brTaxableNew">{{ money($breakdown['taxable_new'] ?? 0) }}</td></tr>
<tr><td>Tax + cess</td><td id="brTaxOld">{{ money($filing->tax_old_regime) }}</td><td id="brTaxNew">{{ money($filing->tax_new_regime) }}</td></tr>
<tr><td>TDS deducted</td><td id="brTdsOld">{{ money($tds) }}</td><td id="brTdsNew">{{ money($tds) }}</td></tr>
<tr class="itr-row-strong"><td>Refund / (Payable)</td>
    <td id="brNetOld">{{ money($breakdown['payable_or_refund_old'] ?? 0) }}</td>
    <td id="brNetNew">{{ money($breakdown['payable_or_refund_new'] ?? 0) }}</td>
</tr>
</table></div></div>

<div class="itr-actions-row">
@if($filing->status === 'customer_summary')
<button class="itr-btn itr-btn-orange itr-btn-lg" formaction="{{ route('user.approve-summary', $filing) }}" formmethod="post" type="submit">
    {!! icon('check') !!} Approve &amp; continue
</button>
<button class="itr-btn itr-btn-outline" type="submit">Save changes</button>
@else
<button class="itr-btn itr-btn-orange itr-btn-lg" type="submit">
    {{ $filing->filing_mode === 'self' ? 'Continue to confirm' : 'Save summary' }}
</button>
@endif
<a class="itr-btn itr-btn-outline" href="{{ route('user.track', $filing) }}">Track</a>
</div>
</form>
@if($filing->status === 'customer_summary' && !empty($prefill['expert_summary_note']))
<div class="itr-alert itr-alert-info itr-mt-md"><strong>Expert note:</strong> {{ $prefill['expert_summary_note'] }}</div>
@endif
@endsection
