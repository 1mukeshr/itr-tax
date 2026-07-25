@extends('layouts.auth')

@section('title', 'Register')

@section('content')
@php
    $mode = $mode ?? request('mode');
    $planId = $planId ?? request('plan_id');
    $loginParams = array_filter(['mode' => $mode, 'plan_id' => $planId]);
@endphp

<div class="itr-auth-head">
    <h2>Create account</h2>
    <p>Register once - then complete your profile and open your dashboard.</p>
</div>

@if($errors->any())
    <div class="itr-alert itr-alert-error">{{ $errors->first() }}</div>
@endif

<form class="itr-auth-form" method="post" action="{{ route('register') }}" novalidate>
    @csrf
    <input type="hidden" name="account_type" value="user">
    @if(!empty($mode))
        <input type="hidden" name="mode" value="{{ $mode }}">
    @endif
    @if(!empty($planId))
        <input type="hidden" name="plan_id" value="{{ $planId }}">
    @endif

    <div class="itr-field">
        <label for="reg-name">Full name</label>
        <input id="reg-name" class="itr-input" name="name" value="{{ old('name') }}" required placeholder="As per PAN" autocomplete="name">
    </div>
    <div class="itr-field">
        <label for="reg-email">Email</label>
        <input id="reg-email" class="itr-input" type="email" name="email" value="{{ old('email') }}" required placeholder="name@email.com" autocomplete="username">
    </div>

    <div class="itr-field-grid">
        <div class="itr-field">
            <label for="reg-phone">Phone</label>
            <input id="reg-phone" class="itr-input" name="phone" value="{{ old('phone') }}" placeholder="10-digit mobile" autocomplete="tel">
        </div>
        <div class="itr-field">
            <label for="reg-pan">PAN</label>
            <input id="reg-pan" class="itr-input itr-pan-input" name="pan" value="{{ old('pan') }}" maxlength="10" placeholder="ABCDE1234F">
        </div>
    </div>

    <div class="itr-field-grid">
        <div class="itr-field">
            <label for="reg-password">Password</label>
            <input id="reg-password" class="itr-input" type="password" name="password" required placeholder="Min 6 characters" autocomplete="new-password">
        </div>
        <div class="itr-field">
            <label for="reg-password-confirm">Confirm password</label>
            <input id="reg-password-confirm" class="itr-input" type="password" name="password_confirmation" required placeholder="Repeat password" autocomplete="new-password">
        </div>
    </div>

    <button class="itr-btn itr-btn-orange itr-btn-block itr-btn-lg" type="submit">Create account</button>
</form>

<div class="itr-auth-links">
    <p>Already registered? <a href="{{ route('login', $loginParams) }}">Sign in</a></p>
</div>
@endsection
