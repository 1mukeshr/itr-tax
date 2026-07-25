@extends('layouts.panel')

@section('title', 'Payments')

@section('content')
<div class="itr-page-title">
    <h1>Payments</h1>
    <p>Successful and pending checkout records across expert plans.</p>
</div>
<div class="itr-card">
<div class="itr-table-wrap">
<table>
<thead>
<tr>
    <th>Txn</th>
    <th>User</th>
    <th>Amount</th>
    <th>Coupon</th>
    <th>Status</th>
    <th>Date</th>
</tr>
</thead>
<tbody>
@forelse($payments as $p)
<tr>
    <td><span class="itr-table-id">{{ $p->transaction_id }}</span></td>
    <td>
        <div class="itr-admin-cell-main">{{ $p->user->name ?? '-' }}</div>
        <div class="itr-admin-cell-sub">{{ $p->user->email ?? '' }}</div>
    </td>
    <td><span class="itr-table-money">{{ money($p->amount) }}</span></td>
    <td>{{ $p->coupon_code ?? '—' }}</td>
    <td>{!! statusBadge($p->status === 'success' ? 'completed' : 'payment_pending') !!}</td>
    <td class="itr-table-muted">{{ formatDate($p->paid_at ?? $p->created_at) }}</td>
</tr>
@empty
<tr><td colspan="6" class="itr-empty">No payments yet.</td></tr>
@endforelse
</tbody>
</table>
</div>
@include('partials.pager', ['paginator' => $payments])
</div>
@endsection
