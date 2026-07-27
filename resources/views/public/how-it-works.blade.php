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
            @forelse(($processSelf ?? collect())->take(4) as $step)
                <li>{!! icon('check') !!} {{ $step->title }}</li>
            @empty
                <li>{!! icon('check') !!} Upload Form 16 / AIS / 26AS</li>
                <li>{!! icon('check') !!} Enter figures &amp; review tax summary</li>
                <li>{!! icon('check') !!} Generate filing reference &amp; e-verify tips</li>
            @endforelse
        </ul>
        <a class="itr-btn itr-btn-primary" href="{{ filingStartUrl('self') }}">{!! icon('spark') !!} Start Self Filing</a>
    </div>
    <div class="itr-box">
        <span class="itr-tag">Hire a Tax Expert</span>
        <h3 class="itr-title-spaced">Expert-assisted filing</h3>
        <p>Ideal for investors, traders, freelancers, NRIs and anyone who wants a tax expert to review and file.</p>
        <ul class="itr-check-list">
            @forelse(($processAssisted ?? collect())->take(4) as $step)
                <li>{!! icon('check') !!} {{ $step->title }}</li>
            @empty
                <li>{!! icon('check') !!} Pick Basic / Standard / Premium</li>
                <li>{!! icon('check') !!} Confirm plan checkout &amp; get tax expert match</li>
                <li>{!! icon('check') !!} Track till ACK is ready</li>
            @endforelse
        </ul>
        <a class="itr-btn itr-btn-orange" href="{{ filingStartUrl('assisted') }}">{!! icon('users') !!} Hire a Tax Expert</a>
    </div>
</div>
</div></section>

<section class="itr-section itr-alt"><div class="itr-container">
<div class="itr-section-title">
    <h2>Simple process for each path</h2>
    <p>The same journey you follow after you start filing on {{ $app['name'] }}.</p>
</div>
<div class="itr-grid-2">
    <div class="itr-box">
        <h3 class="itr-title-spaced">Self Filing</h3>
        @include('partials.process-steps', ['steps' => $processSelf ?? collect(), 'processMode' => 'self', 'class' => 'itr-process-stack', 'numbered' => true])
        <a class="itr-btn itr-btn-primary itr-mt-md" href="{{ filingStartUrl('self') }}">{!! icon('spark') !!} Start Self Filing</a>
    </div>
    <div class="itr-box">
        <h3 class="itr-title-spaced">Hire a Tax Expert</h3>
        @include('partials.process-steps', ['steps' => $processAssisted ?? collect(), 'processMode' => 'assisted', 'class' => 'itr-process-stack', 'numbered' => true])
        <a class="itr-btn itr-btn-orange itr-mt-md" href="{{ filingStartUrl('assisted') }}">{!! icon('users') !!} Hire a Tax Expert</a>
    </div>
</div>
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
