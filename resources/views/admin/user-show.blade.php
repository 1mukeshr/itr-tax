@extends('layouts.panel')

@section('title', $user->name)

@section('content')
<div class="itr-page-title itr-admin-user-head">
    <div>
        <p class="itr-help itr-mb-sm"><a href="{{ route('admin.users', ['role' => $user->role === 'admin' ? 'admin' : 'user']) }}">← Users</a></p>
        <h1>{{ $user->name }}</h1>
        <p>
            {{ $user->email }}
            · <span class="itr-badge {{ $user->role === 'admin' ? 'itr-badge-info' : 'itr-badge-muted' }}">{{ roleLabel($user->role) }}</span>
            @if($user->status === 'active')
                <span class="itr-badge itr-badge-success">Active</span>
            @else
                <span class="itr-badge itr-badge-danger">{{ ucfirst($user->status) }}</span>
            @endif
        </p>
    </div>
    <div class="itr-admin-user-actions">
        <form method="post" action="{{ route('admin.toggle-user', $user) }}" class="itr-inline-form">
            @csrf
            <button class="itr-btn itr-btn-outline" type="submit" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                {{ $user->status === 'active' ? 'Suspend account' : 'Activate account' }}
            </button>
        </form>
    </div>
</div>

<div class="itr-admin-user-kpis">
    <div class="itr-admin-user-kpi">
        <span>Orders</span>
        <strong>{{ (int) $stats['orders'] }}</strong>
    </div>
    <div class="itr-admin-user-kpi">
        <span>Complete ITR</span>
        <strong>{{ (int) $stats['completed'] }}</strong>
    </div>
    <div class="itr-admin-user-kpi">
        <span>Pending</span>
        <strong>{{ (int) $stats['pending'] }}</strong>
    </div>
    <div class="itr-admin-user-kpi">
        <span>Paid total</span>
        <strong>{{ money($stats['paid']) }}</strong>
    </div>
</div>

<div class="itr-admin-user-grid">
    <div class="itr-card">
        <div class="itr-card-h">Profile details</div>
        <div class="itr-card-b">
            <dl class="itr-admin-detail-list">
                <div><dt>Full name</dt><dd>{{ $user->name }}</dd></div>
                <div><dt>Email</dt><dd>{{ $user->email }}</dd></div>
                <div><dt>Phone</dt><dd>{{ $user->phone ?: '—' }}</dd></div>
                <div><dt>PAN</dt><dd>{{ $user->pan ?: '—' }}</dd></div>
                <div><dt>City</dt><dd>{{ $user->city ?: '—' }}</dd></div>
                <div><dt>State</dt><dd>{{ $user->state ?: '—' }}</dd></div>
                <div><dt>Pincode</dt><dd>{{ $user->pincode ?: '—' }}</dd></div>
                <div><dt>Address</dt><dd>{{ $user->address ?: '—' }}</dd></div>
                <div><dt>Joined</dt><dd>{{ optional($user->created_at)->format('d M Y, h:i A') ?: '—' }}</dd></div>
                <div><dt>Email verified</dt><dd>{{ $user->email_verified_at ? optional($user->email_verified_at)->format('d M Y') : 'Not verified' }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="itr-card">
        <div class="itr-card-h">Recent payments</div>
        <div class="itr-table-wrap">
            <table>
                <thead>
                <tr><th>Txn</th><th>Amount</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                @forelse($payments as $p)
                    <tr>
                        <td>{{ $p->transaction_id ?: ('#'.$p->id) }}</td>
                        <td class="itr-row-strong">{{ money($p->amount) }}</td>
                        <td>
                            @if($p->status === 'success')
                                <span class="itr-badge itr-badge-success">Success</span>
                            @else
                                <span class="itr-badge itr-badge-muted">{{ ucfirst($p->status) }}</span>
                            @endif
                        </td>
                        <td>{{ optional($p->paid_at ?? $p->created_at)->format('d M Y') ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="itr-empty">No payments yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="itr-card itr-mt-md">
    <div class="itr-card-h">
        Orders / filings
        <a class="itr-help" href="{{ route('admin.orders', ['q' => $user->email]) }}">Open in All Orders →</a>
    </div>
    <div class="itr-table-wrap">
        <table class="itr-admin-orders-table">
            <thead>
            <tr>
                <th>Order</th>
                <th>ITR</th>
                <th>Plan</th>
                <th>Mode</th>
                <th>Expert</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Updated</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($filings as $o)
                <tr>
                    <td class="itr-row-strong">#{{ $o->id }}</td>
                    <td>{{ $o->itr_type ?: '—' }}</td>
                    <td>{{ $o->plan->name ?? '—' }}</td>
                    <td>{{ ($o->filing_mode ?? '') === 'assisted' ? 'Tax Expert' : 'Self' }}</td>
                    <td>{{ $o->ca->name ?? '—' }}</td>
                    <td class="itr-row-strong">{{ money($o->amount) }}</td>
                    <td>{!! statusBadge($o->status) !!}</td>
                    <td>{{ optional($o->updated_at)->format('d M, h:i A') ?? '—' }}</td>
                    <td>
                        <a class="itr-btn itr-btn-outline itr-btn-sm" href="{{ route('admin.orders', ['status' => $o->status === 'filed' || $o->status === 'completed' ? 'complete' : $o->status, 'q' => $o->id]) }}">Open</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="itr-empty">No filings for this customer.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('partials.pager', ['paginator' => $filings])
</div>
@endsection
