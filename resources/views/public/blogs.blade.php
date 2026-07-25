@extends('layouts.main')

@section('content')
<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Knowledge Hub</span>
        <h1>ITR Guides &amp; Resources</h1>
        <p>Practical guides for FY {{ $app['financial_year'] }} — regimes, Form 16 checks, AIS mismatches and filing tips.</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container">
@if($blogs->isEmpty())
<div class="itr-empty-state">
    {!! iconBox('pen') !!}
    <h3>Guides coming soon</h3>
    <p>We’re publishing filing tips for this assessment year. Meanwhile, start your return or browse FAQs.</p>
    <div class="itr-gap-row itr-gap-row-center">
        <a class="itr-btn itr-btn-orange" href="{{ filingStartUrl('self') }}">{!! icon('spark') !!} Start Filing</a>
        <a class="itr-btn itr-btn-outline" href="{{ url('/faqs') }}">{!! icon('help') !!} Read FAQs</a>
    </div>
</div>
@else
<div class="itr-grid-3">
@foreach($blogs as $blog)
    @include('partials.blog-card', ['blog' => $blog])
@endforeach
</div>
@include('partials.pager', ['paginator' => $blogs])
@endif

<div class="itr-cta-band itr-mt-lg">
    <h2>Ready to put the guide into action?</h2>
    <p>Upload Form 16 and get a clear tax summary for AY {{ $app['assessment_year'] }}.</p>
    <div class="itr-cta-actions">
        <a class="itr-btn itr-btn-orange" href="{{ filingStartUrl('self') }}">{!! icon('spark') !!} Start Filing Now</a>
        <a class="itr-btn itr-btn-white" href="{{ url('/how-it-works') }}">{!! icon('list') !!} See how it works</a>
    </div>
</div>
</div></section>
@endsection
