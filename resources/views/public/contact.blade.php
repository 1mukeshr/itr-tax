@extends('layouts.main')

@section('title', 'Contact')

@section('content')

<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Support</span>
        <h1>We’re here to help you file</h1>
        <p>Questions on documents, payments, expert assignment or filing status? Reach the {{ $app['name'] }} desk during support hours.</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container">
<div class="itr-contact-grid">
    <div class="itr-contact-channels">
        <div class="itr-channel">
            {!! iconBox('mail') !!}
            <div>
                <h3>Email support</h3>
                <p>{{ $app['support_email'] }}</p>
                <div class="itr-help">Best for document doubts &amp; account help</div>
            </div>
        </div>
        @if(!empty($app['support_phone']))
        <div class="itr-channel">
            {!! iconBox('phone') !!}
            <div>
                <h3>Phone / WhatsApp desk</h3>
                <p>{{ $app['support_phone'] }}</p>
                <div class="itr-help">Mon–Sat · 9 AM – 9 PM IST</div>
            </div>
        </div>
        @endif
        <div class="itr-channel">
            {!! iconBox('building') !!}
            <div>
                <h3>Office</h3>
                <p>{{ $app['company_address'] ?? 'Bengaluru, India' }}</p>
                <div class="itr-help">Correspondence address</div>
            </div>
        </div>
        <div class="itr-box">
            <h3>Before you write in</h3>
            <ul class="itr-tip-list">
                <li>{!! icon('file') !!} Keep your filing ID handy (Dashboard → Track)</li>
                <li>{!! icon('upload') !!} Attach Form 16 / AIS screenshots if relevant</li>
                <li>{!! icon('users') !!} Mention Self vs Expert mode and plan name</li>
            </ul>
        </div>
    </div>

    <div class="itr-card">
        <div class="itr-card-h">{!! icon('message') !!} Send us a message</div>
        <div class="itr-card-b">
            <form method="post" action="{{ url('/contact') }}">
                @csrf
                <div class="itr-form-group">
                    <label>Full name</label>
                    <input class="itr-form-control" name="name" required placeholder="Your name">
                </div>
                <div class="itr-form-group">
                    <label>Email</label>
                    <input class="itr-form-control" type="email" name="email" required placeholder="you@email.com">
                </div>
                <div class="itr-form-group">
                    <label>How can we help?</label>
                    <textarea class="itr-form-control" name="message" rows="5" required placeholder="Describe your question — filing status, documents, refund, payment…"></textarea>
                </div>
                <button class="itr-btn itr-btn-primary itr-btn-block" type="submit">{!! icon('mail') !!} Send message</button>
                <p class="itr-help itr-mt-sm">We aim to reply within one business day during filing season.</p>
            </form>
        </div>
    </div>
</div>
</div></section>

@endsection
