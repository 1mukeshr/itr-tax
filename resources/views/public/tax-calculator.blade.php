@extends('layouts.main')

@section('title', 'Tax Calculator')

@section('content')

<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Free tool</span>
        <h1>Income Tax Calculator</h1>
        <p>Simplified tax estimate under old vs new regime for FY {{ $app['financial_year'] }}. Includes standard deduction, basic slabs, §87A where applicable, and 4% cess — not a complete tax computation.</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container">
<div class="itr-calc-grid">
    <div class="itr-card">
        <div class="itr-card-h">{!! icon('chart') !!} Your income details</div>
        <div class="itr-card-b">
            <div class="itr-form-group">
                <label>Gross total income (₹)</label>
                <input class="itr-form-control" type="number" id="calcGross" value="" min="0" step="1000" placeholder="Enter gross income">
            </div>
            <div class="itr-form-group">
                <label>Deductions 80C/80D etc. (₹) — old regime</label>
                <input class="itr-form-control" type="number" id="calcDeduct" value="150000" min="0" step="1000">
            </div>
            <button class="itr-btn itr-btn-primary" type="button" id="calcBtn">{!! icon('spark') !!} Calculate tax</button>
            <p class="itr-help itr-mt-sm">Uses the same simplified logic as the filing Tax Summary step. Excludes surcharge, marginal relief, and special rates (e.g. LTCG).</p>
        </div>
    </div>
    <div class="itr-card">
        <div class="itr-card-h">{!! icon('rupee') !!} Results</div>
        <div class="itr-card-b">
            <div class="itr-grid-2">
                <div class="itr-box">
                    <div class="itr-help">Old regime tax</div>
                    <div class="itr-price itr-price-md" id="calcOld">₹0.00</div>
                </div>
                <div class="itr-box">
                    <div class="itr-help">New regime tax</div>
                    <div class="itr-price itr-price-md" id="calcNew">₹0.00</div>
                </div>
            </div>
            <div class="itr-alert itr-alert-success itr-mt-md" id="calcRec">Enter income and calculate.</div>
            <div class="itr-actions-row">
                <a class="itr-btn itr-btn-orange" href="{{ filingStartUrl('self') }}">{!! icon('spark') !!} Start Filing</a>
                <a class="itr-btn itr-btn-outline" href="{{ filingStartUrl('assisted') }}">{!! icon('users') !!} Hire Tax Expert</a>
            </div>
        </div>
    </div>
</div>
</div></section>

@endsection
