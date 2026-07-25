@extends('layouts.panel')

@section('title', 'Tax Expert Dashboard')

@section('content')
@php
    $priority = $clients
        ->reject(fn ($c) => in_array($c->status, ['filed', 'completed', 'cancelled'], true))
        ->sortBy(function ($c) {
            return match ($c->status) {
                'customer_approved', 'ready_to_file' => 1,
                'assigned' => 2,
                'under_review' => 3,
                'docs_requested' => 4,
                'customer_summary' => 5,
                default => 9,
            };
        })
        ->values()
        ->take(10);
@endphp
<div class="itr-page-title">
    <h1>Work queue</h1>
    <p>Review docs → send summary → after customer approval, mark filed and upload ACK.</p>
</div>
<div class="itr-welcome-actions itr-mb-md">
    <a class="itr-btn itr-btn-orange" href="{{ route('ca.clients', ['status' => 'customer_approved']) }}">Ready to file</a>
    <a class="itr-btn itr-btn-white" href="{{ route('ca.clients', ['status' => 'assigned']) }}">New assignments</a>
    <a class="itr-btn itr-btn-white" href="{{ route('ca.clients') }}">All clients</a>
</div>

<div class="itr-stats">
    <div class="itr-stat itr-stat-accent"><div class="l">Assigned</div><div class="v">{{ (int) $stats['assigned'] }}</div></div>
    <div class="itr-stat"><div class="l">In review</div><div class="v">{{ (int) $stats['review'] }}</div></div>
    <div class="itr-stat"><div class="l">Filed</div><div class="v">{{ (int) $stats['filed'] }}</div></div>
    <div class="itr-stat"><div class="l">Doc requests</div><div class="v">{{ (int) $stats['pending_docs'] }}</div></div>
</div>

<div class="itr-card"><div class="itr-card-h">Needs your action <a href="{{ route('ca.clients') }}">All</a></div>
<div class="itr-table-wrap"><table>
<thead>
<tr><th>Client</th><th>Plan</th><th>Status</th><th>Your next action</th><th></th></tr>
</thead>
<tbody>
@forelse($priority as $c)
<tr>
    <td>
        <div class="itr-admin-cell-main">{{ $c->user->name ?? '-' }}</div>
        <div class="itr-admin-cell-sub">Filing #{{ $c->id }}</div>
    </td>
    <td>{{ $c->plan->name ?? '—' }}</td>
    <td>{!! statusBadge($c->status) !!}</td>
    <td class="itr-table-muted">{{ expertNextAction($c) }}</td>
    <td><a class="itr-btn itr-btn-primary itr-btn-sm" href="{{ route('ca.filing', $c) }}">Open</a></td>
</tr>
@empty
<tr><td colspan="5" class="itr-empty">No clients assigned yet — new expert filings appear here after customer payment.</td></tr>
@endforelse
</tbody>
</table></div></div>
@endsection
