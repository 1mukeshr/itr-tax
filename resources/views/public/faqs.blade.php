@extends('layouts.main')

@section('content')
@php
    $faqCatIcons = [
        'All' => 'list',
        'General' => 'help',
        'Process' => 'clock',
        'Documents' => 'file',
        'Refund' => 'wallet',
    ];
@endphp
<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Help Centre</span>
        <h1>Frequently Asked Questions</h1>
        <p>Everything you need to know about e-filing on {{ $app['name'] }} — documents, regimes, expert plans and refunds.</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container itr-container-narrow">
<div class="itr-faq-cats">
    @foreach($faqCatIcons as $label => $iconName)
    <button type="button" class="itr-faq-cat {{ $label === 'All' ? 'itr-active' : '' }}" data-faq-filter="{{ $label }}">
        <span class="itr-faq-cat-ico">{!! icon($iconName) !!}</span>
        <span>{{ $label }}</span>
    </button>
    @endforeach
</div>

@if($faqs->isEmpty())
<div class="itr-empty-state">
    {!! iconBox('help') !!}
    <h3>No FAQs yet</h3>
    <p>Check back soon, or write to support if you need help right away.</p>
    <a class="itr-btn itr-btn-primary" href="{{ url('/contact') }}">{!! icon('mail') !!} Contact support</a>
</div>
@else
@foreach($faqs as $faq)
@php
    $cat = $faq->category ?? 'General';
    $catIcon = $faqCatIcons[$cat] ?? 'help';
@endphp
<details class="itr-faq" data-faq-cat="{{ $cat }}">
    <summary>
        <span class="itr-faq-qico" aria-hidden="true">{!! icon($catIcon) !!}</span>
        <span class="itr-faq-qtext">{{ $faq->question }}</span>
        <span class="itr-faq-toggle" aria-hidden="true">{!! icon('chevron-down') !!}</span>
    </summary>
    <div class="itr-faq-body">
        <p>{!! nl2br(e($faq->answer)) !!}</p>
    </div>
</details>
@endforeach
@endif

<div class="itr-cta-band itr-mt-lg">
    <h2>Still have a question?</h2>
    <p>Our support team helps with documents, payments, expert matching and filing status.</p>
    <div class="itr-cta-actions">
        <a class="itr-btn itr-btn-orange" href="{{ url('/contact') }}">{!! icon('mail') !!} Write to support</a>
        <a class="itr-btn itr-btn-white" href="{{ filingStartUrl('self') }}">{!! icon('spark') !!} Start Filing</a>
    </div>
</div>
</div></section>
@endsection
