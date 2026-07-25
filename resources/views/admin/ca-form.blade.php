@extends('layouts.panel')

@section('title', $ca ? 'Edit Tax Expert' : 'Add Tax Expert')

@section('content')
@php $edit = !empty($ca); @endphp
<div class="itr-page-title">
    <h1>{{ $edit ? 'Edit Tax Expert' : 'Add Tax Expert' }}</h1>
    <p>{{ $edit ? 'Update profile, availability and login status.' : 'Or use the quick form on the Tax Experts list.' }}</p>
</div>
<div class="itr-card"><div class="itr-card-b">
<form method="post" action="{{ $edit ? route('admin.cas.update', $ca->id) : route('admin.cas.store') }}">
@csrf
<div class="itr-form-row">
    <div class="itr-form-group"><label>Name</label><input class="itr-form-control" name="name" required value="{{ old('name', $ca->name ?? '') }}"></div>
    <div class="itr-form-group"><label>Email</label>
        @if($edit)
            <input class="itr-form-control" value="{{ $ca->email }}" disabled>
        @else
            <input class="itr-form-control" type="email" name="email" required value="{{ old('email') }}">
        @endif
    </div>
</div>
<div class="itr-form-row">
    <div class="itr-form-group"><label>Phone</label><input class="itr-form-control" name="phone" value="{{ old('phone', $ca->phone ?? '') }}"></div>
    <div class="itr-form-group"><label>Password {{ $edit ? '(leave blank to keep)' : '' }}</label>
        <input class="itr-form-control" type="password" name="password" {{ $edit ? '' : 'required minlength=6' }}>
    </div>
</div>
<div class="itr-form-row">
    <div class="itr-form-group"><label>Membership No</label><input class="itr-form-control" name="membership_no" value="{{ old('membership_no', $ca->membership_no ?? '') }}"></div>
    <div class="itr-form-group"><label>Specialization</label><input class="itr-form-control" name="specialization" value="{{ old('specialization', $ca->specialization ?? '') }}"></div>
</div>
<div class="itr-form-row">
    <div class="itr-form-group"><label>Experience (years)</label><input class="itr-form-control" type="number" name="experience_years" min="0" value="{{ old('experience_years', $ca->experience_years ?? '0') }}"></div>
    <div class="itr-form-group"><label>Max clients</label><input class="itr-form-control" type="number" name="max_clients" min="1" value="{{ old('max_clients', $ca->max_clients ?? '50') }}"></div>
</div>
<div class="itr-form-group"><label>Bio</label><textarea class="itr-form-control" name="bio" rows="3">{{ old('bio', $ca->bio ?? '') }}</textarea></div>
@if($edit)
<div class="itr-form-row">
    <div class="itr-form-group"><label>Status</label>
        <select class="itr-form-control" name="status">
            <option value="active" {{ ($ca->status ?? '') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="pending" {{ ($ca->status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="inactive" {{ ($ca->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            <option value="suspended" {{ ($ca->status ?? '') === 'suspended' ? 'selected' : '' }}>Suspended</option>
        </select>
    </div>
    <div class="itr-form-group"><label>Available for new work</label>
        <select class="itr-form-control" name="is_available">
            <option value="1" {{ !empty($ca->is_available) ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ empty($ca->is_available) ? 'selected' : '' }}>No</option>
        </select>
    </div>
</div>
@endif
<button class="itr-btn itr-btn-orange" type="submit">Save tax expert</button>
<a class="itr-btn itr-btn-outline" href="{{ route('admin.cas') }}">Back to list</a>
</form>
</div></div>
@endsection
