@extends('layouts.main')

@section('title', 'Tools')

@section('content')

<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Tax tools</span>
        <h1>Tax tools that make filing easier</h1>
        <p>Calculators and helpers for FY {{ $app['financial_year'] }} — free to explore before you file.</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container">
<div class="itr-grid-3">
    <a class="itr-tool-card" href="{{ url('/tax-calculator') }}">
        {!! iconBox('chart') !!}
        <h3>Income Tax Calculator</h3>
        <p>Compare old vs new regime tax payable instantly.</p>
        <span class="itr-link-more">Open tool {!! icon('arrow-right') !!}</span>
    </a>
    <a class="itr-tool-card" href="{{ route('tools.hra') }}">
        {!! iconBox('building') !!}
        <h3>HRA Calculator</h3>
        <p>Estimate HRA exemption under classic old-regime rules.</p>
        <span class="itr-link-more">Open tool {!! icon('arrow-right') !!}</span>
    </a>
    <a class="itr-tool-card" href="{{ route('tools.rent-receipt') }}">
        {!! iconBox('file') !!}
        <h3>Rent receipt</h3>
        <p>Generate a printable rent receipt for landlord / HRA records.</p>
        <span class="itr-link-more">Open tool {!! icon('arrow-right') !!}</span>
    </a>
    <a class="itr-tool-card" href="{{ url('/refund-status') }}">
        {!! iconBox('rupee') !!}
        <h3>Filing Status</h3>
        <p>Look up your ITR Tax filing by PAN and acknowledgement, plus ITD refund tips.</p>
        <span class="itr-link-more">Check status {!! icon('arrow-right') !!}</span>
    </a>
    <a class="itr-tool-card" href="{{ url('/how-it-works') }}">
        {!! iconBox('list') !!}
        <h3>Filing journey map</h3>
        <p>See Self vs Expert steps before you start.</p>
        <span class="itr-link-more">View journey {!! icon('arrow-right') !!}</span>
    </a>
    <a class="itr-tool-card" href="{{ url('/blogs/old-vs-new-tax-regime') }}">
        {!! iconBox('spark') !!}
        <h3>Regime guide</h3>
        <p>Understand how to compare old vs new regime before filing.</p>
        <span class="itr-link-more">Read guide {!! icon('arrow-right') !!}</span>
    </a>
    <a class="itr-tool-card" href="{{ url('/blogs/form-16-vs-form-26as') }}">
        {!! iconBox('file') !!}
        <h3>Form 16 vs 26AS</h3>
        <p>Checklist to avoid TDS mismatches.</p>
        <span class="itr-link-more">Read guide {!! icon('arrow-right') !!}</span>
    </a>
    <a class="itr-tool-card" href="{{ url('/pricing') }}">
        {!! iconBox('users') !!}
        <h3>Expert plan finder</h3>
        <p>Basic, Standard or Premium — pick based on complexity.</p>
        <span class="itr-link-more">View plans {!! icon('arrow-right') !!}</span>
    </a>
</div>
</div></section>

@endsection
