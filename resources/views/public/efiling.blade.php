@extends('layouts.main')

@section('content')
<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Income Tax eFiling</span>
        <h1>Prepare your ITR online for FY {{ $app['financial_year'] }}</h1>
        <p>AY {{ $app['assessment_year'] }} — Self Filing with guided summary, or Hire a Tax Expert for assisted review (target within 24 hours after complete docs &amp; payment).</p>
        <div class="itr-banner-actions">
            <a class="itr-btn itr-btn-orange" href="{{ filingStartUrl('self') }}">{!! icon('spark') !!} Start Filing Now</a>
            <a class="itr-btn itr-btn-white" href="{{ filingStartUrl('assisted') }}">{!! icon('users') !!} Hire a Tax Expert</a>
        </div>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container">
<div class="itr-section-title"><h2>Why use {{ $app['name'] }} to file?</h2></div>
<div class="itr-feature-grid">
    <div class="itr-feat">{!! iconBox('upload') !!}<h3>Form 16 upload</h3><p>Start with Form 16, then enter Part B figures on Tax Summary.</p></div>
    <div class="itr-feat">{!! iconBox('chart') !!}<h3>Right regime, clearly shown</h3><p>Old vs new estimated tax side-by-side before you commit.</p></div>
    <div class="itr-feat">{!! iconBox('list') !!}<h3>AIS / 26AS uploads</h3><p>Keep statements in your vault and reconcile TDS yourself before filing.</p></div>
    <div class="itr-feat">{!! iconBox('file') !!}<h3>ITR-1 to ITR-4 guidance</h3><p>Pick the form that matches salary, gains, business or presumptive income.</p></div>
    <div class="itr-feat">{!! iconBox('users') !!}<h3>Expert-assisted plans</h3><p>Assisted plans include tax expert review, notes and acknowledgement upload.</p></div>
    <div class="itr-feat">{!! iconBox('download') !!}<h3>ACK + e-verify reminder</h3><p>Download acknowledgement/reference and e-verify within 30 days on the Income Tax portal.</p></div>
</div>
</div></section>

<section class="itr-section itr-alt"><div class="itr-container">
<div class="itr-section-title"><h2>File ITR in 3 simple steps</h2></div>
<div class="itr-grid-3">
    <div class="itr-box itr-process-card"><span class="itr-process-num">01</span><h3>Upload documents</h3><p>Form 16, AIS, 26AS, proofs.</p></div>
    <div class="itr-box itr-process-card"><span class="itr-process-num">02</span><h3>Review tax summary</h3><p>Compare regimes &amp; deductions.</p></div>
    <div class="itr-box itr-process-card"><span class="itr-process-num">03</span><h3>File or Hire Tax Expert</h3><p>Self-file or pay for tax expert filing.</p></div>
</div>
</div></section>

<section class="itr-section"><div class="itr-container">
<div class="itr-section-title"><h2>Assisted plans</h2></div>
<div class="itr-grid-3">
@foreach($plans as $i => $plan)
@php
    $features = method_exists($plan, 'featuresList') ? ($plan->featuresList() ?: []) : (json_decode($plan->features ?? '[]', true) ?: []);
@endphp
<div class="itr-plan {{ $i === 1 ? 'itr-hot' : '' }}">
    @if($i === 1)<span class="itr-tag">Popular</span>@endif
    <h3 class="itr-plan-name">{{ $plan->name }}</h3>
    <div class="itr-price">
        {{ money($plan->price) }}
    </div>
    <p>{{ $plan->description }}</p>
    <ul>
        @foreach(array_slice($features, 0, 4) as $f)
            <li>{!! icon('check') !!} {{ $f }}</li>
        @endforeach
    </ul>
    <a class="itr-btn {{ $i === 1 ? 'itr-btn-primary' : 'itr-btn-outline' }} itr-btn-block" href="{{ filingStartUrl('assisted', (int) $plan->id) }}">Get started</a>
</div>
@endforeach
</div>
</div></section>

@if(!empty($faqs) && count($faqs))
<section class="itr-section itr-alt"><div class="itr-container itr-container-narrow">
<div class="itr-section-title"><h2>eFiling FAQs</h2></div>
@foreach($faqs as $faq)
<details class="itr-faq"><summary>{{ $faq->question }}</summary><p>{!! nl2br(e($faq->answer)) !!}</p></details>
@endforeach
</div></section>
@endif
@endsection
