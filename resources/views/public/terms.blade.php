@extends('layouts.main')

@section('content')
<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Legal</span>
        <h1>Terms of use</h1>
        <p>Rules for using the {{ $app['name'] }} e-filing portal.</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container itr-container-narrow itr-legal">
    <h3>1. Service</h3>
    <p>{{ $app['name'] }} helps you prepare and manage income-tax filings with self or expert-assisted workflows. Tax calculations are simplified estimates (not formal tax advice) and may exclude items such as surcharge, special rates or marginal relief. Not affiliated with the Income Tax Department.</p>
    <h3>2. Your responsibilities</h3>
    <p>You must provide accurate PAN, income and document details. Self-prepare steps generate an ITR Tax filing reference and do not by themselves create an Income Tax Department acknowledgement. You remain responsible for official e-filing and e-verification on the Income Tax portal.</p>
    <h3>3. Payments</h3>
    <p>Expert plan fees are shown at checkout. Coupons apply only when valid. Confirming checkout records the order in {{ $app['name'] }} and enables tax-expert assignment. Live card/UPI collection requires a configured payment gateway.</p>
    <h3>4. Tax experts</h3>
    <p>Assigned experts review your documents, may request more information, share a tax summary for your approval, then file and upload the acknowledgement number they obtain.</p>
    <h3>5. Contact</h3>
    <p>Questions: {{ $app['support_email'] }}</p>
</div></section>
@endsection
