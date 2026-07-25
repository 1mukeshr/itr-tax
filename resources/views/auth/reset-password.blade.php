@extends('layouts.auth')

@section('title', 'Reset password')

@section('content')
<div class="itr-auth-head">
    <h2>Reset password</h2>
    <p>Choose a new password for your account.</p>
</div>

@if(session('error') || $errors->any())
    <div class="itr-alert itr-alert-error">{{ session('error') ?? $errors->first() }}</div>
@endif

<form class="itr-auth-form" method="post" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="itr-field">
        <label for="email">Email</label>
        <input id="email" class="itr-input" type="email" name="email" required value="{{ old('email', $email) }}" autocomplete="username">
    </div>
    <div class="itr-field">
        <label for="password">New password</label>
        <input id="password" class="itr-input" type="password" name="password" required minlength="6" autocomplete="new-password">
    </div>
    <div class="itr-field">
        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" class="itr-input" type="password" name="password_confirmation" required minlength="6" autocomplete="new-password">
    </div>
    <button class="itr-btn itr-btn-orange itr-btn-block itr-btn-lg" type="submit">Update password</button>
</form>
@endsection
