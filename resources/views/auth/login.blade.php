@extends('layouts.auth')

@section('title', !empty($isAdminPortal) ? 'Admin Login' : 'Login')

@section('content')
@php
    $mode = request('mode');
    $planId = request('plan_id');
    $registerParams = array_filter(['mode' => $mode, 'plan_id' => $planId]);
    $isAdminPortal = !empty($isAdminPortal);
    $portalSeparated = !empty($portalSeparated);
@endphp
<div class="itr-auth-head">
    <h2>{{ $isAdminPortal ? 'Admin sign in' : 'Sign in' }}</h2>
    <p>
        @if($isAdminPortal)
            Admin portal only — manage orders, experts, and settings.
        @else
            Customers and tax experts — use your registered email and password.
        @endif
    </p>
</div>

@if(session('error') || $errors->any())
    <div class="itr-alert itr-alert-error">{{ session('error') ?? $errors->first() }}</div>
@elseif(session('info'))
    <div class="itr-alert itr-alert-info">{{ session('info') }}</div>
@elseif(session('success'))
    <div class="itr-alert itr-alert-success">{{ session('success') }}</div>
@endif

<form class="itr-auth-form" method="post" action="{{ url('/login') }}" novalidate>
    @csrf
    @if($mode && ! $isAdminPortal)<input type="hidden" name="mode" value="{{ $mode }}">@endif
    @if($planId && ! $isAdminPortal)<input type="hidden" name="plan_id" value="{{ $planId }}">@endif

    <div class="itr-field">
        <label for="login-email">{{ $isAdminPortal ? 'Admin ID' : 'Email' }}</label>
        <input
            id="login-email"
            class="itr-input"
            type="{{ $isAdminPortal ? 'text' : 'email' }}"
            name="email"
            required
            placeholder="{{ $isAdminPortal ? 'admin' : 'name@email.com' }}"
            value="{{ old('email') }}"
            autocomplete="username"
        >
    </div>
    <div class="itr-field">
        <label for="login-password">Password</label>
        <input id="login-password" class="itr-input" type="password" name="password" required placeholder="••••••••" autocomplete="current-password">
    </div>

    <div class="itr-auth-row">
        <label class="itr-check"><input type="checkbox" name="remember" value="1"> Remember me</label>
        @if(! $isAdminPortal)
            <a href="{{ route('password.request') }}">Forgot password?</a>
        @endif
    </div>

    <button class="itr-btn itr-btn-orange itr-btn-block itr-btn-lg" type="submit">
        {{ $isAdminPortal ? 'Sign in to admin' : 'Sign in' }}
    </button>
</form>

@if(! $isAdminPortal)
<div class="itr-auth-links">
    <p>New here? <a href="{{ route('register', $registerParams) }}">Create a customer account</a></p>
    <p class="itr-help">Tax experts are added by admin from the admin portal.</p>
</div>
@elseif($portalSeparated)
<div class="itr-auth-links">
    <p class="itr-help">Customers and experts sign in on the main site only.</p>
</div>
@endif
@endsection
