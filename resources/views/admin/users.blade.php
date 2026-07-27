@extends('layouts.panel')

@section('title', 'Users')

@section('content')
<div class="itr-page-title">
    <h1>Users</h1>
    <p>Manage customer and admin accounts. Tax experts are under <a href="{{ route('admin.cas') }}">Tax Experts</a>.</p>
</div>

<div class="itr-tabs">
    <a class="{{ $role === 'all' ? 'itr-active' : '' }}" href="{{ route('admin.users', ['role' => 'all', 'q' => $q]) }}">All ({{ $counts['all'] }})</a>
    <a class="{{ $role === 'user' ? 'itr-active' : '' }}" href="{{ route('admin.users', ['role' => 'user', 'q' => $q]) }}">Customers ({{ $counts['user'] }})</a>
    <a class="{{ $role === 'admin' ? 'itr-active' : '' }}" href="{{ route('admin.users', ['role' => 'admin', 'q' => $q]) }}">Admins ({{ $counts['admin'] }})</a>
    <a href="{{ route('admin.cas') }}">Tax Experts →</a>
</div>

<form method="get" class="itr-search-form">
    <input type="hidden" name="role" value="{{ $role }}">
    <input class="itr-form-control itr-search-input" name="q" value="{{ $q }}" placeholder="Search name, email, phone, PAN…">
    <button class="itr-btn itr-btn-primary" type="submit">Search</button>
</form>

<div class="itr-card"><div class="itr-table-wrap"><table>
<thead>
<tr><th>Name</th><th>Email</th><th>Role</th><th>Phone</th><th>Status</th><th></th></tr>
</thead>
<tbody>
@forelse($users as $u)
<tr>
    <td>
        <a class="itr-admin-cell-main" href="{{ route('admin.users.show', $u) }}">{{ $u->name }}</a>
    </td>
    <td>{{ $u->email }}</td>
    <td><span class="itr-badge {{ $u->role === 'admin' ? 'itr-badge-info' : ($u->role === 'ca' ? 'itr-badge-warn' : 'itr-badge-muted') }}">{{ roleLabel($u->role) }}</span></td>
    <td>{{ $u->phone ?: '—' }}</td>
    <td>
        @if($u->status === 'active')
            <span class="itr-badge itr-badge-success">Active</span>
        @else
            <span class="itr-badge itr-badge-danger">{{ ucfirst($u->status) }}</span>
        @endif
    </td>
    <td>
        <a class="itr-btn itr-btn-outline itr-btn-sm" href="{{ route('admin.users.show', $u) }}">View</a>
        @if($u->role === 'ca')
            <a class="itr-btn itr-btn-outline itr-btn-sm" href="{{ route('admin.cas.edit', $u->id) }}">Edit tax expert</a>
        @endif
        <form method="post" action="{{ route('admin.toggle-user', $u) }}" class="itr-inline-form">
            @csrf
            <button class="itr-btn itr-btn-outline itr-btn-sm" type="submit" {{ $u->id === auth()->id() ? 'disabled' : '' }}>{{ $u->status === 'active' ? 'Suspend' : 'Activate' }}</button>
        </form>
    </td>
</tr>
@empty
<tr><td colspan="6" class="itr-empty">No accounts found.</td></tr>
@endforelse
</tbody>
</table></div>
@include('partials.pager', ['paginator' => $users])
</div>
@endsection
