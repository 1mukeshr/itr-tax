@extends('layouts.panel')

@section('title', 'Assigned Clients')

@section('content')
<div class="itr-panel-hero">
    <div>
        <h1>Assigned clients</h1>
        <p>Filter by status, then open a filing and follow the next action.</p>
    </div>
</div>
<div class="itr-tabs">
    <a class="{{ empty($filter) ? 'itr-active' : '' }}" href="{{ route('ca.clients') }}">All</a>
    @foreach([
        'assigned' => 'New',
        'under_review' => 'In review',
        'docs_requested' => 'Docs needed',
        'customer_summary' => 'Awaiting approve',
        'customer_approved' => 'Ready to file',
        'filed' => 'Filed',
        'completed' => 'Completed',
    ] as $key => $label)
        <a class="{{ ($filter ?? '') === $key ? 'itr-active' : '' }}" href="{{ route('ca.clients', ['status' => $key]) }}">{{ $label }}</a>
    @endforeach
</div>
<div class="itr-card"><div class="itr-table-wrap"><table>
<thead>
<tr><th>Client</th><th>Plan</th><th>ITR</th><th>Status</th><th>Next action</th><th></th></tr>
</thead>
<tbody>
@forelse($clients as $c)
<tr>
    <td>
        <div class="itr-admin-cell-main">{{ $c->user->name ?? '-' }}</div>
        <div class="itr-admin-cell-sub">{{ $c->user->email ?? '' }}</div>
    </td>
    <td>{{ $c->plan->name ?? '—' }}</td>
    <td>{{ $c->itr_type ?? '—' }}</td>
    <td>{!! statusBadge($c->status) !!}</td>
    <td class="itr-table-muted">{{ expertNextAction($c) }}</td>
    <td><a class="itr-btn itr-btn-primary itr-btn-sm" href="{{ route('ca.filing', $c) }}">Open</a></td>
</tr>
@empty
<tr><td colspan="6" class="itr-empty">No clients in this filter.</td></tr>
@endforelse
</tbody>
</table></div>
@include('partials.pager', ['paginator' => $clients])
</div>
@endsection
