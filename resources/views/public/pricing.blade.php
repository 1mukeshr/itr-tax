@extends('layouts.main')

@section('title', 'Pricing')

@section('content')
<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">FY {{ $app['financial_year'] }} · AY {{ $app['assessment_year'] }}</span>
        <h1>Transparent pricing for every taxpayer</h1>
        <p>Prepare free on Self mode, or choose an assisted plan with dedicated tax expert review. Target turnaround is within 24 hours after complete documents and payment.</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container">
<div class="itr-section-title">
    <h2>Self vs Hire a Tax Expert</h2>
    <p>Pick the mode that matches how complex your income is this year.</p>
</div>
<div class="itr-table-wrap itr-mb-md">
<table class="itr-compare-table">
    <tr>
        <th>What’s included</th>
        <th>Self Filing</th>
        <th>Hire Tax Expert</th>
    </tr>
    <tr>
        <td>Form 16 / AIS / 26AS upload</td>
        <td class="itr-ok-mark">{!! icon('check') !!}</td>
        <td class="itr-ok-mark">{!! icon('check') !!}</td>
    </tr>
    <tr>
        <td>Tax summary &amp; old vs new regime</td>
        <td class="itr-ok-mark">{!! icon('check') !!}</td>
        <td class="itr-ok-mark">{!! icon('check') !!}</td>
    </tr>
    <tr>
        <td>Dedicated tax expert review</td>
        <td class="itr-no-mark">-</td>
        <td class="itr-ok-mark">{!! icon('check') !!}</td>
    </tr>
    <tr>
        <td>Capital gains / F&amp;O / ESOP support</td>
        <td class="itr-no-mark">Limited</td>
        <td class="itr-ok-mark">{!! icon('check') !!}</td>
    </tr>
    <tr>
        <td>Filing + acknowledgement tracking</td>
        <td class="itr-ok-mark">{!! icon('check') !!}</td>
        <td class="itr-ok-mark">{!! icon('check') !!}</td>
    </tr>
    <tr>
        <td>Typical turnaround</td>
        <td>Same day (self)</td>
        <td>Within 24 hours*</td>
    </tr>
</table>
</div>
<p class="itr-text-center itr-help">*Turnaround applies after documents and payment are complete.</p>
</div></section>

<section class="itr-section itr-alt"><div class="itr-container">
<div class="itr-section-title">
    <h2>Assisted Filing Plans</h2>
    <p>Matched with an available tax expert after payment — priced clearly, billed once per return.</p>
</div>
<div class="itr-grid-3">
@foreach($plans as $i => $plan)
@php
    $features = method_exists($plan, 'featuresList') ? ($plan->featuresList() ?: []) : (json_decode($plan->features ?? '[]', true) ?: []);
@endphp
<div class="itr-plan {{ $i === 1 ? 'itr-hot' : '' }}">
    @if($i === 1)
        <span class="itr-tag">Most Popular</span>
    @elseif($i === 0)
        <span class="itr-tag itr-tag-orange">Starter</span>
    @else
        <span class="itr-tag">Premium</span>
    @endif
    <h3 class="itr-plan-name">{{ $plan->name }}</h3>
    <div class="itr-price">
        {{ money($plan->price) }}
    </div>
    <p>{{ $plan->description }}</p>
    <ul>
        @foreach($features as $f)
            <li>{!! icon('check') !!} {{ $f }}</li>
        @endforeach
    </ul>
    <a class="itr-btn {{ $i === 1 ? 'itr-btn-primary' : 'itr-btn-outline' }} itr-btn-block" href="{{ filingStartUrl('assisted', (int) $plan->id) }}">Get started</a>
</div>
@endforeach
</div>

<div class="itr-soft-note itr-mt-lg">
    Tip: Use coupon <strong>SAVE10</strong> for 10% off (min ₹999) or <strong>FLAT500</strong> for ₹500 off on plans from ₹2,499.
</div>

<div class="itr-cta-band itr-mt-lg">
    <h2>Not sure which plan fits?</h2>
    <p>Start with Self Filing for simple Form 16 cases. Upgrade to an expert anytime if capital gains, F&amp;O or foreign income appear.</p>
    <div class="itr-cta-actions">
        <a class="itr-btn itr-btn-orange" href="{{ filingStartUrl('self') }}">Start Filing Now</a>
        <a class="itr-btn itr-btn-white" href="{{ url('/contact') }}">Talk to support</a>
    </div>
</div>
</div></section>

<section class="itr-section"><div class="itr-container">
<div class="itr-section-title">
    <h2>What’s included in every assisted return</h2>
</div>
<div class="itr-feature-row">
    <div class="itr-feature-mini">{!! iconBox('list') !!}<h3>Document checklist</h3><p>Clear list of Form 16, AIS, proofs and statements you need.</p></div>
    <div class="itr-feature-mini">{!! iconBox('users') !!}<h3>Expert match</h3><p>Tax expert assignment after successful payment.</p></div>
    <div class="itr-feature-mini">{!! iconBox('message') !!}<h3>Review notes</h3><p>Tax expert comments, doc requests and status updates in one place.</p></div>
    <div class="itr-feature-mini">{!! iconBox('download') !!}<h3>ACK download</h3><p>Download acknowledgement once your return is filed.</p></div>
</div>
</div></section>
@endsection
