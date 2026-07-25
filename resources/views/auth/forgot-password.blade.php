@extends('layouts.auth')

@section('title', 'Forgot password')

@section('content')
<div class="itr-auth-head">
    <h2>Forgot password</h2>
    <p>Enter your account email. We’ll send a reset link (or show it in local/demo mode).</p>
</div>

@if(session('error') || $errors->any())
    <div class="itr-alert itr-alert-error">{{ session('error') ?? $errors->first() }}</div>
@elseif(session('success'))
    <div class="itr-alert itr-alert-success">{{ session('success') }}</div>
@endif

@if(session('demo_reset_url'))
    <div class="itr-alert itr-alert-info">
        Demo mailer active. Open reset link:
        <a href="{{ session('demo_reset_url') }}">Reset password</a>
    </div>
@endif

<form class="itr-auth-form" method="post" action="{{ route('password.email') }}">
    @csrf
    <div class="itr-field">
        <label for="email">Email</label>
        <input id="email" class="itr-input" type="email" name="email" required value="{{ old('email') }}" autocomplete="username">
    </div>
    <button class="itr-btn itr-btn-orange itr-btn-block itr-btn-lg" type="submit">Send reset link</button>
</form>

<div class="itr-auth-links">
    <p><a href="{{ route('login') }}">Back to sign in</a></p>
</div>
@endsection
