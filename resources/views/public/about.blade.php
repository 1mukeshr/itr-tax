@extends('layouts.main')

@section('title', 'About')

@section('content')

<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Company</span>
        <h1>About {{ $app['name'] }}</h1>
        <p>An income-tax filing workspace — built to make Form 16 → summary → self-prepare or Hire Tax Expert clear and transparent.</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container">
<div class="itr-grid-2">
    <div class="itr-box">
        <h2>Our mission</h2>
        <p>Help every Indian taxpayer prepare returns with clarity — regime comparison, guided checklists, and expert help when income gets complex.</p>
        <ul class="itr-check-list">
            <li>{!! icon('check') !!} Self Filing for salaried Form 16 cases</li>
            <li>{!! icon('check') !!} Expert plans for investors, traders &amp; NRIs</li>
            <li>{!! icon('check') !!} Secure vault, Tax expert workspace &amp; admin controls</li>
        </ul>
    </div>
    <div class="itr-box">
        <h2>What you can do on ITR Tax</h2>
        <p>Full product flow: marketing site, auth, user filing journey, tax expert review desk and admin operations.</p>
        <div class="itr-grid-2 itr-mt-md">
            <div class="itr-feature-mini">{!! iconBox('spark') !!}<h3>Product</h3><p>eFiling UX</p></div>
            <div class="itr-feature-mini">{!! iconBox('shield') !!}<h3>Trust</h3><p>Role security</p></div>
        </div>
    </div>
</div>
<div class="itr-cta-band itr-mt-lg">
    <h2>Ready to experience the filing flow?</h2>
    <p>Create an account and start with Form 16 in minutes.</p>
    <div class="itr-cta-actions">
        <a class="itr-btn itr-btn-orange" href="{{ filingStartUrl('self') }}">{!! icon('spark') !!} Start Filing</a>
        <a class="itr-btn itr-btn-white" href="{{ url('/contact') }}">{!! icon('mail') !!} Contact us</a>
    </div>
</div>
</div></section>

@endsection
