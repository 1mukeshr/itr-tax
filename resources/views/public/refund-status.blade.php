@extends('layouts.main')

@section('title', 'Filing Status')

@section('content')

<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Refunds</span>
        <h1>Check ITR filing status</h1>
        <p>Look up your filing on ITR Tax using PAN and acknowledgement number. Refund credit is processed by the Income Tax Department after e-verification.</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container itr-container-form">
<div class="itr-card">
    <div class="itr-card-h">{!! icon('search') !!} Look up acknowledgement</div>
    <div class="itr-card-b">
        <form method="post" action="{{ route('refund-status.check') }}">
            @csrf
            <div class="itr-form-group">
                <label>PAN</label>
                <input class="itr-form-control itr-pan-input" name="pan" maxlength="10" placeholder="ABCDE1234F" value="{{ old('pan') }}" required>
            </div>
            <div class="itr-form-group">
                <label>Acknowledgement number</label>
                <input class="itr-form-control" name="acknowledgement_no" placeholder="ACK number" value="{{ old('acknowledgement_no') }}" required>
            </div>
            <button class="itr-btn itr-btn-primary itr-btn-block" type="submit">{!! icon('search') !!} Check status</button>
        </form>
        <div class="itr-soft-note itr-mt-md">Logged-in users can also open <a href="{{ route('login') }}">My Filings → Track</a> for live status and ACK download.</div>
    </div>
</div>
</div></section>

@endsection
