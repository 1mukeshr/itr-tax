<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login') | {{ $app['name'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/itr-tax.css') }}?v={{ @filemtime(public_path('assets/css/itr-tax.css')) ?: time() }}">
    <link rel="icon" href="{{ asset('assets/images/itr-tax-logo.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/itr-tax-logo.png') }}">
</head>
<body class="itr-tax itr-auth-body">
@php
    $adminHost = \App\Support\Portal::isAdminHost();
    $homeUrl = $adminHost ? url('/login') : url('/');
@endphp
<div class="itr-auth-page">
    <div class="itr-auth-stage">
        <aside class="itr-auth-aside" aria-hidden="false">
            <a class="itr-auth-brand" href="{{ $homeUrl }}">{!! brandLogo('light') !!}</a>
            <p class="itr-auth-kicker">
                @if($adminHost)
                    Admin portal · FY {{ $app['financial_year'] }}
                @else
                    FY {{ $app['financial_year'] }} · AY {{ $app['assessment_year'] }}
                @endif
            </p>
            <h1 class="itr-auth-aside-title">
                {{ $adminHost ? 'Operations console' : 'File ITR with clarity and confidence' }}
            </h1>
            <p class="itr-auth-aside-lead">
                @if($adminHost)
                    Manage filings, tax experts, and support - connected to the same live database as the customer site.
                @else
                    Secure vault, regime comparison, and tax expert support - in one place.
                @endif
            </p>
            @if(! $adminHost)
            <ul class="itr-auth-points">
                <li>{!! icon('check') !!} Form 16, AIS &amp; 26AS vault</li>
                <li>{!! icon('check') !!} Old vs new regime summary</li>
                <li>{!! icon('check') !!} Expert review in 24 hours</li>
            </ul>
            @endif
        </aside>
        <main class="itr-auth-main">
            <div class="itr-auth-card">
                @yield('content')
            </div>
            @if($adminHost)
                <p class="itr-auth-back"><a href="{{ \App\Support\Portal::publicBaseUrl() }}">← Main customer site</a></p>
            @else
                <p class="itr-auth-back"><a href="{{ url('/') }}">← Back to home</a></p>
            @endif
        </main>
    </div>
</div>
<script src="{{ asset('assets/js/itr-tax.js') }}"></script>
</body>
</html>
