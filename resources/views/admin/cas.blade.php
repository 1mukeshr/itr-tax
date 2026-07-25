@extends('layouts.panel')

@section('title', 'Tax Experts')

@section('content')
<div class="itr-page-title">
    <h1>Tax Experts</h1>
    <p>Add experts here only. After a client pays, assign them from <a href="{{ route('admin.orders', ['status' => 'paid']) }}">All Orders → Need expert</a>.</p>
</div>

<div class="itr-soft-note itr-mb-md">
    <strong>How it works:</strong>
    1) Add tax expert below →
    2) Client pays for assisted filing →
    3) Open <em>Need expert</em> orders → select expert → Assign.
    Experts log in on the main site with this email/password (not the admin portal).
</div>

<div class="itr-card itr-mb-md">
    <div class="itr-card-h">Add tax expert</div>
    <div class="itr-card-b">
        <form method="post" action="{{ route('admin.cas.store') }}" class="itr-form">
            @csrf
            <div class="itr-form-row">
                <div class="itr-form-group">
                    <label>Full name</label>
                    <input class="itr-form-control" name="name" required value="{{ old('name') }}" placeholder="e.g. Priya Sharma">
                </div>
                <div class="itr-form-group">
                    <label>Login email</label>
                    <input class="itr-form-control" type="email" name="email" required value="{{ old('email') }}" placeholder="expert@email.com">
                </div>
            </div>
            <div class="itr-form-row">
                <div class="itr-form-group">
                    <label>Phone</label>
                    <input class="itr-form-control" name="phone" value="{{ old('phone') }}" placeholder="10-digit mobile">
                </div>
                <div class="itr-form-group">
                    <label>Password</label>
                    <input class="itr-form-control" type="password" name="password" required minlength="6" placeholder="Min 6 characters">
                </div>
            </div>
            <div class="itr-form-row">
                <div class="itr-form-group">
                    <label>Membership No <span class="itr-help">(optional)</span></label>
                    <input class="itr-form-control" name="membership_no" value="{{ old('membership_no') }}" placeholder="Membership ID">
                </div>
                <div class="itr-form-group">
                    <label>Specialization <span class="itr-help">(optional)</span></label>
                    <input class="itr-form-control" name="specialization" value="{{ old('specialization') }}" placeholder="Salary, capital gains…">
                </div>
            </div>
            <div class="itr-form-row">
                <div class="itr-form-group">
                    <label>Experience (years)</label>
                    <input class="itr-form-control" type="number" name="experience_years" min="0" value="{{ old('experience_years', 0) }}">
                </div>
                <div class="itr-form-group">
                    <label>Max clients</label>
                    <input class="itr-form-control" type="number" name="max_clients" min="1" value="{{ old('max_clients', 50) }}">
                </div>
            </div>
            <button class="itr-btn itr-btn-orange" type="submit">{!! icon('plus') !!} Add tax expert</button>
        </form>
    </div>
</div>

@if($cas->isEmpty())
    <div class="itr-alert itr-alert-warn">No tax experts yet. Add one above - then you can assign paid orders.</div>
@endif

<div class="itr-card">
    <div class="itr-card-h">Tax expert list <span class="itr-help">{{ method_exists($cas, 'total') ? $cas->total() : $cas->count() }} total</span></div>
    <div class="itr-table-wrap">
        <table>
            <thead>
            <tr>
                <th>Expert</th>
                <th>Membership</th>
                <th>Specialization</th>
                <th>Clients</th>
                <th>Status</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($cas as $c)
                <tr>
                    <td>
                        <div class="itr-admin-cell-main">{{ $c->name }}</div>
                        <div class="itr-admin-cell-sub">{{ $c->email }}{{ $c->phone ? ' · '.$c->phone : '' }}</div>
                    </td>
                    <td>{{ $c->membership_no ?: '—' }}</td>
                    <td>{{ $c->specialization ?: '—' }}</td>
                    <td><span class="itr-table-id">{{ (int) $c->client_count }}</span> <span class="itr-table-muted">/ {{ (int) ($c->max_clients ?: 50) }}</span></td>
                    <td>
                        @if($c->status === 'active')
                            <span class="itr-badge itr-badge-success">Active</span>
                        @else
                            <span class="itr-badge itr-badge-danger">{{ ucfirst($c->status) }}</span>
                        @endif
                        @if(empty($c->is_available))
                            <span class="itr-badge itr-badge-muted">Unavailable</span>
                        @endif
                    </td>
                    <td>
                        @if($c->status !== 'active')
                            <form method="post" action="{{ route('admin.cas.activate', $c->id) }}" class="itr-inline-form">
                                @csrf
                                <button class="itr-btn itr-btn-orange itr-btn-sm" type="submit">Activate</button>
                            </form>
                        @endif
                        <a class="itr-btn itr-btn-outline itr-btn-sm" href="{{ route('admin.cas.edit', $c->id) }}">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="itr-empty">No tax experts added yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('partials.pager', ['paginator' => $cas])
</div>
@endsection
