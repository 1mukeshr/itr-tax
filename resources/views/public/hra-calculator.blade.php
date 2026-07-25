@extends('layouts.main')

@section('title', 'HRA Calculator')

@section('content')
<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Free tool</span>
        <h1>HRA exemption calculator</h1>
        <p>Estimate house rent allowance exemption under the classic old-regime rules (least of three conditions).</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container">
<div class="itr-calc-grid">
    <div class="itr-card">
        <div class="itr-card-h">{!! icon('building') !!} Your details</div>
        <div class="itr-card-b">
            <form method="post" action="{{ route('tools.hra.compute') }}">
                @csrf
                <div class="itr-form-group">
                    <label>Basic salary (annual ₹)</label>
                    <input class="itr-form-control" type="number" name="basic" min="0" step="1" required value="{{ old('basic', $input['basic'] ?? '') }}">
                </div>
                <div class="itr-form-group">
                    <label>HRA received (annual ₹)</label>
                    <input class="itr-form-control" type="number" name="hra_received" min="0" step="1" required value="{{ old('hra_received', $input['hra_received'] ?? '') }}">
                </div>
                <div class="itr-form-group">
                    <label>Rent paid (annual ₹)</label>
                    <input class="itr-form-control" type="number" name="rent_paid" min="0" step="1" required value="{{ old('rent_paid', $input['rent_paid'] ?? '') }}">
                </div>
                <div class="itr-form-group">
                    <label>City type</label>
                    <select class="itr-form-control" name="metro">
                        <option value="0" @selected(old('metro', $input['metro'] ?? '0') === '0')>Non-metro (40% of basic)</option>
                        <option value="1" @selected(old('metro', $input['metro'] ?? '0') === '1')>Metro (50% of basic)</option>
                    </select>
                </div>
                <button class="itr-btn itr-btn-primary" type="submit">Calculate HRA</button>
            </form>
            <p class="itr-help itr-mt-sm">Simplified estimate for planning only — not a full tax computation.</p>
        </div>
    </div>
    <div class="itr-card">
        <div class="itr-card-h">{!! icon('rupee') !!} Result</div>
        <div class="itr-card-b">
            @if($result)
                <div class="itr-grid-2">
                    <div class="itr-box">
                        <div class="itr-help">Exempt HRA</div>
                        <div class="itr-price itr-price-md">{{ money($result['exempt']) }}</div>
                    </div>
                    <div class="itr-box">
                        <div class="itr-help">Taxable HRA</div>
                        <div class="itr-price itr-price-md">{{ money($result['taxable']) }}</div>
                    </div>
                </div>
                <ul class="itr-tip-list itr-mt-md">
                    <li>Actual HRA: {{ money($result['rules']['actual_hra']) }}</li>
                    <li>% of basic: {{ money($result['rules']['salary_percent']) }}</li>
                    <li>Rent − 10% of basic: {{ money($result['rules']['rent_minus_10']) }}</li>
                </ul>
            @else
                <div class="itr-empty">Enter figures to see exemption.</div>
            @endif
            <a class="itr-btn itr-btn-outline itr-mt-md" href="{{ route('tools') }}">All tools</a>
        </div>
    </div>
</div>
</div></section>
@endsection
