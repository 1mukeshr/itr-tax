@extends('layouts.main')

@section('title', 'How It Works')

@section('content')

<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Clear filing journey</span>
        <h1>How {{ $app['name'] }} works</h1>
        <p>Two ways to file for FY {{ $app['financial_year'] }} — Self Filing for simple returns, or Hire a Tax Expert when income gets complex.</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container">
<div class="itr-section-title">
    <h2>Choose your filing mode</h2>
    <p>Start with the path that matches your comfort and complexity.</p>
</div>
<div class="itr-grid-2">
    <div class="itr-box">
        <span class="itr-tag itr-tag-orange">Self ITR Filing</span>
        <h3 class="itr-title-spaced">File yourself in minutes</h3>
        <p>Best for salaried taxpayers with Form 16 and straightforward deductions. You stay in control from upload to e-verify.</p>
        <ul class="itr-check-list">
            <li>{!! icon('check') !!} Upload Form 16 / AIS / 26AS</li>
            <li>{!! icon('check') !!} Enter figures &amp; review tax summary</li>
            <li>{!! icon('check') !!} Compare old vs new regime</li>
            <li>{!! icon('check') !!} Generate filing reference &amp; e-verify tips</li>
        </ul>
        <a class="itr-btn itr-btn-primary" href="{{ filingStartUrl('self') }}">{!! icon('spark') !!} Start Self Filing</a>
    </div>
    <div class="itr-box">
        <span class="itr-tag">Hire a Tax Expert</span>
        <h3 class="itr-title-spaced">Expert-assisted filing</h3>
        <p>Ideal for investors, traders, freelancers, NRIs and anyone who wants a tax expert to review and file.</p>
        <ul class="itr-check-list">
            <li>{!! icon('check') !!} Pick Basic / Standard / Premium</li>
            <li>{!! icon('check') !!} Confirm plan checkout &amp; get tax expert match</li>
            <li>{!! icon('check') !!} Respond to doc requests</li>
            <li>{!! icon('check') !!} Track till ACK is ready</li>
        </ul>
        <a class="itr-btn itr-btn-orange" href="{{ filingStartUrl('assisted') }}">{!! icon('users') !!} Hire a Tax Expert</a>
    </div>
</div>
</div></section>

<section class="itr-section itr-alt"><div class="itr-container">
<div class="itr-section-title">
    <h2>End-to-end journey</h2>
    <p>Every filing follows the same clear stages — so you always know what’s next.</p>
</div>
<div class="itr-journey">
    <div class="itr-journey-step">
        <div class="itr-journey-num">{!! icon('user') !!}</div>
        <div class="itr-journey-body">
            <h3>Create account &amp; choose mode</h3>
            <p>Sign up with email/PAN, select Self or Hire Tax Expert, and tell us your income profile (salaried, investor, NRI, etc.).</p>
        </div>
    </div>
    <div class="itr-journey-step">
        <div class="itr-journey-num">{!! icon('upload') !!}</div>
        <div class="itr-journey-body">
            <h3>Upload Form 16 &amp; proofs</h3>
            <p>Add Form 16, AIS/26AS, interest certificates and investment proofs to your secure document vault.</p>
        </div>
    </div>
    <div class="itr-journey-step">
        <div class="itr-journey-num">{!! icon('chart') !!}</div>
        <div class="itr-journey-body">
            <h3>Review tax summary</h3>
            <p>See gross income, deductions and tax payable under old vs new regime. Pick the option with better savings.</p>
        </div>
    </div>
    <div class="itr-journey-step">
        <div class="itr-journey-num">{!! icon('wallet') !!}</div>
        <div class="itr-journey-body">
            <h3>Self-file or pay for expert</h3>
            <p>Self: confirm preview and generate a filing reference. Expert: complete checkout, get tax expert assignment, and let them review.</p>
        </div>
    </div>
    <div class="itr-journey-step">
        <div class="itr-journey-num">{!! icon('download') !!}</div>
        <div class="itr-journey-body">
            <h3>Track, download ACK &amp; e-verify</h3>
            <p>Follow status updates, download acknowledgement/reference, and e-verify within 30 days on the Income Tax portal.</p>
        </div>
    </div>
</div>
<p class="itr-text-center itr-mt-lg"><a class="itr-btn itr-btn-orange" href="{{ filingStartUrl('self') }}">{!! icon('spark') !!} Start Filing Now</a></p>
</div></section>

<section class="itr-section"><div class="itr-container">
<div class="itr-section-title"><h2>Documents that speed up filing</h2></div>
<div class="itr-grid-3">
    <div class="itr-feature-mini">{!! iconBox('file') !!}<h3>Form 16</h3><p>Salary TDS certificate from your employer — Part A &amp; B.</p></div>
    <div class="itr-feature-mini">{!! iconBox('list') !!}<h3>AIS / 26AS</h3><p>Match interest, TDS and reported income before you file.</p></div>
    <div class="itr-feature-mini">{!! iconBox('rupee') !!}<h3>Investment proofs</h3><p>80C, 80D, home loan, capital gains statements as applicable.</p></div>
</div>
</div></section>

@endsection
