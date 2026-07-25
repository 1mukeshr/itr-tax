@extends('layouts.panel')

@section('title', 'Complete Profile')

@section('content')
<div class="itr-page-title">
    <h1>Complete your profile</h1>
    <p>Step after register — we need PAN and address before filing.</p>
</div>

<div class="itr-flow-strip">
    <span class="itr-flow-on">1. Profile</span>
    <span>2. Choose service</span>
    <span>3. Questions</span>
    <span>4. Documents</span>
    <span>5. Payment</span>
</div>

<div class="itr-card"><div class="itr-card-b">
<form method="post" action="{{ route('user.save-complete-profile') }}">
@csrf
<div class="itr-form-row">
    <div class="itr-form-group"><label>Full name</label><input class="itr-form-control" name="name" value="{{ old('name', $user->name) }}" required></div>
    <div class="itr-form-group"><label>Email</label><input class="itr-form-control" value="{{ $user->email }}" disabled></div>
</div>
<div class="itr-form-row">
    <div class="itr-form-group"><label>Phone *</label><input class="itr-form-control" name="phone" value="{{ old('phone', $user->phone) }}" required placeholder="10-digit mobile"></div>
    <div class="itr-form-group"><label>PAN *</label><input class="itr-form-control itr-pan-input" name="pan" value="{{ old('pan', $user->pan) }}" maxlength="10" required placeholder="ABCDE1234F"></div>
</div>
<div class="itr-form-group"><label>Address</label><textarea class="itr-form-control" name="address" rows="2">{{ old('address', $user->address) }}</textarea></div>
<div class="itr-form-row">
    <div class="itr-form-group"><label>City *</label><input class="itr-form-control" name="city" value="{{ old('city', $user->city) }}" required></div>
    <div class="itr-form-group"><label>State *</label><input class="itr-form-control" name="state" value="{{ old('state', $user->state) }}" required></div>
</div>
<div class="itr-form-group"><label>Pincode</label><input class="itr-form-control" name="pincode" value="{{ old('pincode', $user->pincode) }}"></div>
<button class="itr-btn itr-btn-orange" type="submit">{!! icon('arrow-right') !!} Save &amp; Choose Service</button>
</form>
</div></div>
@endsection
