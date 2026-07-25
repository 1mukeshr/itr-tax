@extends('layouts.main')

@section('title', 'Not Found')

@section('content')

<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Oops</span>
        <h1>Page not found</h1>
        <p>The link may be outdated, or the page moved. Let’s get you back to filing.</p>
    </div>
</div></div>
<section class="itr-section"><div class="itr-container">
<div class="itr-empty-state">
    {!! iconBox('search') !!}
    <h3>We couldn’t find that page</h3>
    <p>Try the homepage, pricing, or jump straight into your dashboard if you’re logged in.</p>
    <div class="itr-gap-row itr-gap-row-center">
        <a class="itr-btn itr-btn-primary" href="{{ url('/') }}">{!! icon('home') !!} Go home</a>
        <a class="itr-btn itr-btn-outline" href="{{ url('/pricing') }}">{!! icon('rupee') !!} View pricing</a>
        <a class="itr-btn itr-btn-orange" href="{{ url('/register') }}">{!! icon('spark') !!} Start Filing</a>
    </div>
</div>
</div></section>

@endsection
