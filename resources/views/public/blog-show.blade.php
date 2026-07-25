@extends('layouts.main')

@section('title', 'Blog Show')

@section('content')

<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">ITR Guide</span>
        <h1>{{ $blog->title }}</h1>
        <p>{{ formatDate($blog->published_at) }} · {{ $blog->author->name ?? 'ITR Tax Editorial' }}</p>
    </div>
</div></div>
<section class="itr-section"><div class="itr-container itr-container-article">
    <figure class="itr-article-cover">
        <img src="{{ $blog->coverUrl() }}" alt="" width="1200" height="640" loading="eager" decoding="async">
    </figure>
    <p class="itr-article-lead">{{ $blog->excerpt }}</p>
    <div class="itr-article-body">{!! nl2br(e($blog->content)) !!}</div>
    <div class="itr-box itr-mt-lg">
        <h3>Next step</h3>
        <p>Apply what you learned — start a filing for FY {{ $app['financial_year'] }} and review your tax summary.</p>
        <div class="itr-gap-row">
            <a class="itr-btn itr-btn-orange" href="{{ filingStartUrl('self') }}">Start Filing</a>
            <a class="itr-btn itr-btn-outline" href="{{ url('/blogs') }}">More guides</a>
        </div>
    </div>
    <p class="itr-back-link"><a href="{{ url('/blogs') }}">← Back to all guides</a></p>
</div></section>

@endsection
