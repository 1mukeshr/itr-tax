@extends('layouts.panel')

@section('title', 'Profile')

@section('content')

<div class="itr-panel-hero">
    <div>
        <h1>My Profile</h1>
        <p>Keep PAN and contact details updated for smooth e-filing.</p>
    </div>
</div>
<div class="itr-card"><div class="itr-card-b">
<form method="post" action="{{ route('user.update-profile') }}">
@csrf
<div class="itr-form-row">
    <div class="itr-form-group"><label>Name</label><input class="itr-form-control" name="name" value="{{ $user->name }}" required></div>
    <div class="itr-form-group"><label>Email</label><input class="itr-form-control" value="{{ $user->email }}" disabled></div>
</div>
<div class="itr-form-row">
    <div class="itr-form-group"><label>Phone</label><input class="itr-form-control" name="phone" value="{{ $user->phone }}"></div>
    <div class="itr-form-group"><label>PAN</label><input class="itr-form-control itr-pan-input" name="pan" value="{{ $user->pan }}" maxlength="10"></div>
</div>
<div class="itr-form-group"><label>Address</label><textarea class="itr-form-control" name="address" rows="2">{{ $user->address }}</textarea></div>
<div class="itr-form-row">
    <div class="itr-form-group"><label>City</label><input class="itr-form-control" name="city" value="{{ $user->city }}"></div>
    <div class="itr-form-group"><label>State</label><input class="itr-form-control" name="state" value="{{ $user->state }}"></div>
</div>
<div class="itr-form-row">
    <div class="itr-form-group"><label>Pincode</label><input class="itr-form-control" name="pincode" value="{{ $user->pincode }}"></div>
    <div class="itr-form-group"><label>New password</label><input class="itr-form-control" type="password" name="password" placeholder="Leave blank to keep"></div>
</div>
<button class="itr-btn itr-btn-primary" type="submit">{!! icon('check') !!} Save profile</button>
</form>
</div></div>

@endsection
