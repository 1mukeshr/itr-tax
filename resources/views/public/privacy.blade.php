@extends('layouts.main')

@section('content')
<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Legal</span>
        <h1>Privacy policy</h1>
        <p>How {{ $app['name'] }} handles your data.</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container itr-container-narrow itr-legal">
    <h3>1. Data we collect</h3>
    <p>Account details (name, email, phone, PAN), filing profile, uploaded documents (Form 16, AIS, proofs), payment metadata, and support messages.</p>
    <h3>2. Why we collect it</h3>
    <p>To create filings, compute tax summaries, assign tax experts, process payments, and operate the service.</p>
    <h3>3. Who can see it</h3>
    <p>Documents and filing data are visible to you, your assigned tax expert, and platform admins. We do not sell personal data.</p>
    <h3>4. Contact</h3>
    <p>Questions: {{ $app['support_email'] }}</p>
</div></section>
@endsection
