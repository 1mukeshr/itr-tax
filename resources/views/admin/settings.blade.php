@extends('layouts.panel')

@section('title', 'Settings')

@section('content')
<div class="itr-page-title">
    <h1>Settings</h1>
    <p>Site configuration and your admin login credentials.</p>
</div>

<div class="itr-grid-2">
<div class="itr-card"><div class="itr-card-h">Admin login (ID &amp; password)</div><div class="itr-card-b">
<div class="itr-soft-note itr-mb-md">
    Stored in database table <code>users</code> (email = login ID, password = hashed). Current login: <strong>{{ $admin->email }}</strong>
</div>
<form method="post" action="{{ route('admin.settings.account') }}" autocomplete="off">
@csrf
<div class="itr-form-group">
    <label>Display name</label>
    <input class="itr-form-control" name="name" required value="{{ old('name', $admin->name) }}">
</div>
<div class="itr-form-group">
    <label>Login ID</label>
    <input class="itr-form-control" type="text" name="email" required value="{{ old('email', $admin->email) }}" autocomplete="username" placeholder="admin">
</div>
<div class="itr-form-group">
    <label>Phone</label>
    <input class="itr-form-control" name="phone" value="{{ old('phone', $admin->phone) }}">
</div>
<div class="itr-form-group">
    <label>Current password <span class="itr-help">(required to save to database)</span></label>
    <input class="itr-form-control" type="password" name="current_password" required autocomplete="current-password">
</div>
<div class="itr-form-row">
    <div class="itr-form-group">
        <label>New password</label>
        <input class="itr-form-control" type="password" name="password" minlength="6" autocomplete="new-password" placeholder="Leave blank to keep">
    </div>
    <div class="itr-form-group">
        <label>Confirm new password</label>
        <input class="itr-form-control" type="password" name="password_confirmation" minlength="6" autocomplete="new-password">
    </div>
</div>
<button class="itr-btn itr-btn-orange" type="submit">Save to database</button>
<p class="itr-help itr-mt-sm">Password is never stored as plain text — only a hash in <code>users.password</code>.</p>
</form>
</div></div>

<div class="itr-card"><div class="itr-card-h">Site settings</div><div class="itr-card-b">
<form method="post" action="{{ route('admin.settings.save') }}">
@csrf
<div class="itr-form-group"><label>Site name</label><input class="itr-form-control" name="site_name" value="{{ $settings['site_name'] ?? $app['name'] }}"></div>
<div class="itr-form-group"><label>Support email</label><input class="itr-form-control" name="support_email" value="{{ $settings['support_email'] ?? '' }}"></div>
<div class="itr-form-group"><label>Support phone</label><input class="itr-form-control" name="support_phone" value="{{ $settings['support_phone'] ?? '' }}"></div>
<div class="itr-form-group"><label>Razorpay key</label><input class="itr-form-control" name="razorpay_key" value="{{ $settings['razorpay_key'] ?? '' }}" autocomplete="off"></div>
<div class="itr-form-group"><label>Razorpay secret</label><input class="itr-form-control" type="password" name="razorpay_secret" value="{{ $settings['razorpay_secret'] ?? '' }}" autocomplete="new-password"></div>
<p class="itr-help">When both key and secret are set, checkout uses live Razorpay. Leave empty for simulated demo payments.</p>
<div class="itr-form-group"><label>Address</label><textarea class="itr-form-control" name="company_address" rows="3">{{ $settings['company_address'] ?? '' }}</textarea></div>
<button class="itr-btn itr-btn-primary" type="submit">Save site settings</button>
</form>
</div></div>
</div>

<div class="itr-card itr-mt-md"><div class="itr-card-h">Plans</div><div class="itr-card-b">
@foreach($plans as $plan)
<form method="post" action="{{ route('admin.plans.update', $plan) }}" class="itr-plan-split">
@csrf
<div class="itr-form-row">
    <div class="itr-form-group"><label>Name</label><input class="itr-form-control" name="name" value="{{ $plan->name }}"></div>
    <div class="itr-form-group"><label>Price</label><input class="itr-form-control" type="number" step="0.01" name="price" value="{{ $plan->price }}"></div>
</div>
<div class="itr-form-group"><label>Description</label><input class="itr-form-control" name="description" value="{{ $plan->description }}"></div>
<label class="itr-check-inline"><input type="checkbox" name="is_active" value="1" {{ $plan->is_active ? 'checked' : '' }}> Active</label>
<button class="itr-btn itr-btn-outline itr-btn-sm" type="submit">Update</button>
</form>
@endforeach
</div></div>
@endsection
