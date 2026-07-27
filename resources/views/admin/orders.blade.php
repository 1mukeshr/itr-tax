@extends('layouts.panel')

@section('title', 'All Orders')

@section('content')
@php
    $groups = [
        '' => 'All Orders',
        'pending' => 'Pending ITR',
        'payment_pending' => 'Checkout',
        'paid' => 'Need tax expert',
        'assigned' => 'Assigned',
        'under_review' => 'In review',
        'docs_requested' => 'Docs needed',
        'customer_summary' => 'Awaiting approve',
        'customer_approved' => 'Ready to file',
        'complete' => 'Complete ITR',
    ];
@endphp
<div class="itr-page-title">
    <h1>All Orders</h1>
    <p>All ITR filings. Filter by stage — paid without a tax expert show under <strong>Need tax expert</strong>.</p>
</div>
@if(!empty($q))
    <div class="itr-alert itr-alert-info">
        Showing results for <strong>{{ $q }}</strong>
        <a href="{{ route('admin.orders', array_filter(['status' => $filter ?: null])) }}">Clear search</a>
    </div>
@endif
<div class="itr-tabs">
    @foreach($groups as $key => $label)
    <a class="{{ ($filter ?: '') === $key ? 'itr-active' : '' }}" href="{{ route('admin.orders', array_filter(['status' => $key ?: null, 'q' => $q ?: null])) }}">{{ $label }}</a>
    @endforeach
</div>
@if($cas->isEmpty())
    <div class="itr-alert itr-alert-warn">No tax experts in the system yet. <a href="{{ route('admin.cas') }}">Add a tax expert</a> first, then assign paid orders here.</div>
@elseif(($filter === 'paid' || !$filter) && !empty($needsExpert))
<div class="itr-alert itr-alert-warn">Paid filings need a tax expert — choose one and click Assign.</div>
@endif
<div class="itr-card">
<div class="itr-table-wrap">
<table class="itr-admin-orders-table">
<thead>
<tr>
    <th>ID</th>
    <th>Client</th>
    <th>ITR</th>
    <th>Plan</th>
    <th>Tax Expert</th>
    <th>Status</th>
    <th>Assign Expert</th>
</tr>
</thead>
<tbody>
@forelse($orders as $o)
<tr class="{{ $o->status === 'paid' && empty($o->ca_id) ? 'itr-row-alert' : '' }}">
    <td><span class="itr-table-id">#{{ $o->id }}</span></td>
    <td>
        <div class="itr-admin-cell-main">
            @if(!empty($o->user_id))
                <a href="{{ route('admin.users.show', $o->user_id) }}">{{ $o->user->name ?? '-' }}</a>
            @else
                {{ $o->user->name ?? '-' }}
            @endif
        </div>
        <div class="itr-admin-cell-sub">{{ $o->user->email ?? '' }}</div>
    </td>
    <td>{{ $o->itr_type }}</td>
    <td>{{ $o->plan->name ?? '-' }}</td>
    <td>{{ $o->ca->name ?? '—' }}</td>
    <td>{!! statusBadge($o->status) !!}</td>
    <td>
        @if(in_array($o->status, ['paid', 'assigned'], true))
        <form method="post" action="{{ route('admin.assign-ca', $o) }}" class="itr-inline-form">
            @csrf
            <select class="itr-form-control itr-select-sm" name="ca_id" required>
                <option value="">Select tax expert</option>
                @foreach($cas as $ca)
                <option value="{{ $ca->id }}" {{ (int) $o->ca_id === (int) $ca->id ? 'selected' : '' }}>{{ $ca->name }}</option>
                @endforeach
            </select>
            <button class="itr-btn {{ $o->status === 'paid' ? 'itr-btn-orange' : 'itr-btn-outline' }} itr-btn-sm" type="submit">{{ $o->status === 'paid' ? 'Assign' : 'Reassign' }}</button>
        </form>
        @else
            <span class="itr-table-muted">{{ $o->ca->name ?? '—' }}</span>
        @endif
    </td>
</tr>
@empty
<tr><td colspan="7" class="itr-empty">No orders in this filter.</td></tr>
@endforelse
</tbody>
</table>
</div>
@include('partials.pager', ['paginator' => $orders])
</div>
@endsection
