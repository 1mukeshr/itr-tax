@extends('layouts.panel')

@section('title', 'Dashboard')

@section('content')
<div class="itr-admin-dash">
    @if($needsExpert > 0)
        <div class="itr-alert itr-alert-warn">
            {{ $needsExpert }} paid ITR order(s) waiting for a tax expert.
            <a href="{{ route('admin.orders', ['status' => 'paid']) }}">Assign now</a>
        </div>
    @endif

    <div class="itr-admin-quick">
        <a class="itr-admin-quick-link" href="{{ route('admin.cas.create') }}">{!! icon('users') !!} Add tax expert</a>
        <a class="itr-admin-quick-link" href="{{ route('admin.dashboard') }}#revenue">{!! icon('chart') !!} Revenue graph</a>
        <a class="itr-admin-quick-link" href="{{ route('admin.orders', ['status' => 'paid']) }}">{!! icon('spark') !!} Assign tax experts</a>
    </div>

    {{-- Core order + revenue cards --}}
    <div class="itr-admin-order-kpis">
        <a class="itr-admin-kpi-card" data-tone="orders" href="{{ route('admin.dashboard', ['orders' => 'all']) }}#orders">
            <div class="itr-admin-kpi-top">
                <span class="itr-admin-kpi-label">All orders</span>
                <span class="itr-admin-kpi-ico" aria-hidden="true">{!! icon('file') !!}</span>
            </div>
            <div class="itr-admin-kpi-value">{{ (int) $stats['all_orders'] }}</div>
            <div class="itr-admin-kpi-meta">{{ (int) $stats['users'] }} customers · {{ (int) $stats['cas'] }} tax experts</div>
        </a>
        <a class="itr-admin-kpi-card itr-admin-kpi-ok" data-tone="done" href="{{ route('admin.orders', ['status' => 'complete']) }}">
            <div class="itr-admin-kpi-top">
                <span class="itr-admin-kpi-label">Complete ITR</span>
                <span class="itr-admin-kpi-ico" aria-hidden="true">{!! icon('check') !!}</span>
            </div>
            <div class="itr-admin-kpi-value">{{ (int) $stats['completed_orders'] }}</div>
            <div class="itr-admin-kpi-meta">
                Today {{ (int) $stats['completed_today'] }} · Week {{ (int) $stats['completed_week'] }} · Month {{ (int) $stats['completed_month'] }}
                · {{ $stats['completion_rate'] }}% rate
            </div>
        </a>
        <a class="itr-admin-kpi-card itr-admin-kpi-warn" data-tone="pending" href="{{ route('admin.orders', ['status' => 'pending']) }}">
            <div class="itr-admin-kpi-top">
                <span class="itr-admin-kpi-label">Pending ITR</span>
                <span class="itr-admin-kpi-ico" aria-hidden="true">{!! icon('list') !!}</span>
            </div>
            <div class="itr-admin-kpi-value">{{ (int) $stats['pending_itr'] }}</div>
            <div class="itr-admin-kpi-meta">
                @if($needsExpert > 0)
                    {{ $needsExpert }} need expert assign
                @else
                    In progress / not filed yet
                @endif
            </div>
        </a>
        <a class="itr-admin-kpi-card itr-admin-kpi-money" data-tone="money" href="{{ route('admin.dashboard') }}#revenue">
            <div class="itr-admin-kpi-top">
                <span class="itr-admin-kpi-label">Revenue</span>
                <span class="itr-admin-kpi-ico" aria-hidden="true">{!! icon('wallet') !!}</span>
            </div>
            <div class="itr-admin-kpi-value">{{ money($stats['revenue']) }}</div>
            <div class="itr-admin-kpi-meta">
                Today {{ money($stats['today_revenue']) }} · Month {{ money($stats['month_revenue']) }}
            </div>
        </a>
        <a class="itr-admin-kpi-card" data-tone="users" href="{{ route('admin.users', ['role' => 'user']) }}">
            <div class="itr-admin-kpi-top">
                <span class="itr-admin-kpi-label">Users</span>
                <span class="itr-admin-kpi-ico" aria-hidden="true">{!! icon('user') !!}</span>
            </div>
            <div class="itr-admin-kpi-value">{{ (int) $stats['users'] }}</div>
            <div class="itr-admin-kpi-meta">
                Today {{ (int) $stats['users_today'] }} · Week {{ (int) $stats['users_week'] }} · Month {{ (int) $stats['users_month'] }}
            </div>
        </a>
    </div>

    {{-- Revenue graph --}}
    <div class="itr-card itr-admin-chart-card" id="revenue">
        <div class="itr-admin-chart-head">
            <div>
                <h3>Revenue, orders &amp; users</h3>
                <p>Payments, new orders, customers, and complete ITR by day, week and month</p>
            </div>
            <div class="itr-admin-chart-tabs" role="tablist" aria-label="Revenue period">
                <button type="button" class="is-active" data-period="day" role="tab" aria-selected="true">Day</button>
                <button type="button" data-period="week" role="tab" aria-selected="false">Week</button>
                <button type="button" data-period="month" role="tab" aria-selected="false">Month</button>
            </div>
        </div>
        <div class="itr-admin-chart-body">
            <div class="itr-admin-chart-canvas-wrap">
                <canvas id="adminPaymentChart" height="110" aria-label="Revenue, orders, users and complete ITR chart"></canvas>
            </div>
            <aside class="itr-admin-chart-aside" aria-label="Period summary">
                <div class="itr-admin-aside-panel">
                    <div class="itr-admin-aside-stat itr-admin-aside-stat--lead">
                        <span>Period revenue</span>
                        <strong id="chartPeriodRevenue">{{ money(array_sum($paymentCharts['day']['revenue'])) }}</strong>
                    </div>
                    <div class="itr-admin-aside-row">
                        <div class="itr-admin-aside-stat" data-tone="payments">
                            <span>Payments</span>
                            <strong id="chartPeriodCount">{{ (int) array_sum($paymentCharts['day']['count']) }}</strong>
                        </div>
                        <div class="itr-admin-aside-stat" data-tone="orders">
                            <span>Orders</span>
                            <strong id="chartPeriodOrders">{{ (int) array_sum($paymentCharts['day']['orders'] ?? []) }}</strong>
                        </div>
                    </div>
                    <div class="itr-admin-aside-row">
                        <div class="itr-admin-aside-stat" data-tone="users">
                            <span>Users</span>
                            <strong id="chartPeriodUsers">{{ (int) array_sum($paymentCharts['day']['users'] ?? []) }}</strong>
                        </div>
                        <div class="itr-admin-aside-stat" data-tone="done">
                            <span>Complete ITR</span>
                            <strong id="chartPeriodCompleted">{{ (int) array_sum($paymentCharts['day']['completed'] ?? []) }}</strong>
                        </div>
                    </div>
                    <div class="itr-admin-aside-divider" role="presentation"></div>
                    <div class="itr-admin-aside-stat itr-admin-aside-stat--meta">
                        <span>Peak period</span>
                        <strong id="chartPeakLabel">-</strong>
                    </div>
                    <div class="itr-admin-aside-row">
                        <div class="itr-admin-aside-stat itr-admin-aside-stat--meta">
                            <span>Peak amount</span>
                            <strong id="chartPeakAmount">{{ money(0) }}</strong>
                        </div>
                        <div class="itr-admin-aside-stat itr-admin-aside-stat--meta">
                            <span>Avg ticket</span>
                            <strong>{{ money($stats['avg_ticket']) }}</strong>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    {{-- Orders table --}}
    <div class="itr-card itr-admin-orders-card" id="orders">
        <div class="itr-admin-orders-head">
            <div>
                <h3>Orders</h3>
                <p>
                    @if($orderFilter === 'pending')
                        Showing pending ITR ({{ $orders->count() }})
                    @elseif($orderFilter === 'completed')
                        Showing complete orders ({{ $orders->count() }})
                    @else
                        Latest {{ $orders->count() }} of {{ (int) $stats['all_orders'] }} orders
                    @endif
                </p>
            </div>
            <div class="itr-admin-orders-tools">
                <div class="itr-admin-chart-tabs" role="tablist" aria-label="Order filter">
                    <a class="{{ $orderFilter === 'all' ? 'is-active' : '' }}" href="{{ route('admin.dashboard', ['orders' => 'all']) }}#orders">All</a>
                    <a class="{{ $orderFilter === 'pending' ? 'is-active' : '' }}" href="{{ route('admin.dashboard', ['orders' => 'pending']) }}#orders">Pending</a>
                    <a class="{{ $orderFilter === 'completed' ? 'is-active' : '' }}" href="{{ route('admin.dashboard', ['orders' => 'completed']) }}#orders">Complete</a>
                </div>
                <a class="itr-btn itr-btn-outline itr-btn-sm" href="{{ route('admin.orders') }}">All Orders</a>
            </div>
        </div>
        <div class="itr-table-wrap">
            <table class="itr-admin-orders-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Client</th>
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
                    @forelse($orders as $o)
                        <tr>
                            <td class="itr-row-strong">#{{ $o->id }}</td>
                            <td>
                                <div class="itr-admin-cell-main">{{ $o->user->name ?? '-' }}</div>
                                <div class="itr-admin-cell-sub">{{ $o->user->email ?? '' }}</div>
                            </td>
                            <td>{{ $o->plan->name ?? '-' }}</td>
                            <td>{{ ($o->filing_mode ?? '') === 'assisted' ? 'Tax Expert' : 'Self' }}</td>
                            <td>{{ $o->ca->name ?? ($o->status === 'paid' ? 'Unassigned' : '-') }}</td>
                            <td class="itr-row-strong">{{ money($o->amount) }}</td>
                            <td>{!! statusBadge($o->status) !!}</td>
                            <td>{{ optional($o->updated_at)->format('d M, h:i A') ?? '-' }}</td>
                            <td>
                                <a class="itr-btn itr-btn-outline itr-btn-sm" href="{{ route('admin.orders', ['status' => $o->status]) }}">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="itr-empty">No orders in this view.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
<script>
(function () {
    var chartData = @json($paymentCharts);
    var canvas = document.getElementById('adminPaymentChart');
    if (!canvas || typeof Chart === 'undefined') return;

    var moneyFmt = new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 0
    });

    function money(v) {
        try { return moneyFmt.format(Number(v) || 0); }
        catch (e) { return '₹' + (Number(v) || 0).toLocaleString('en-IN'); }
    }

    function updateAside(period) {
        var set = chartData[period] || chartData.day;
        var revenue = (set.revenue || []).reduce(function (a, b) { return a + Number(b); }, 0);
        var count = (set.count || []).reduce(function (a, b) { return a + Number(b); }, 0);
        var orders = (set.orders || []).reduce(function (a, b) { return a + Number(b); }, 0);
        var users = (set.users || []).reduce(function (a, b) { return a + Number(b); }, 0);
        var completed = (set.completed || []).reduce(function (a, b) { return a + Number(b); }, 0);
        var peakIdx = 0;
        var peakVal = -1;
        (set.revenue || []).forEach(function (v, i) {
            if (Number(v) > peakVal) { peakVal = Number(v); peakIdx = i; }
        });
        document.getElementById('chartPeriodRevenue').textContent = money(revenue);
        document.getElementById('chartPeriodCount').textContent = String(count);
        var ordersEl = document.getElementById('chartPeriodOrders');
        if (ordersEl) ordersEl.textContent = String(orders);
        var usersEl = document.getElementById('chartPeriodUsers');
        if (usersEl) usersEl.textContent = String(users);
        var doneEl = document.getElementById('chartPeriodCompleted');
        if (doneEl) doneEl.textContent = String(completed);
        document.getElementById('chartPeakLabel').textContent = peakVal > 0 ? (set.labels[peakIdx] || '-') : '-';
        document.getElementById('chartPeakAmount').textContent = money(Math.max(peakVal, 0));
    }

    var chart = new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels: chartData.day.labels,
            datasets: [
                {
                    type: 'line',
                    label: 'Payments',
                    data: chartData.day.count,
                    yAxisID: 'y1',
                    borderColor: '#ff5c35',
                    borderWidth: 2.5,
                    pointRadius: 2,
                    pointHoverRadius: 4,
                    pointBackgroundColor: '#ff5c35',
                    tension: 0.35,
                    fill: false,
                    order: 1
                },
                {
                    type: 'line',
                    label: 'Orders',
                    data: chartData.day.orders || [],
                    yAxisID: 'y1',
                    borderColor: '#2563eb',
                    borderWidth: 2.5,
                    pointRadius: 2,
                    pointHoverRadius: 4,
                    pointBackgroundColor: '#2563eb',
                    tension: 0.35,
                    fill: false,
                    order: 0
                },
                {
                    type: 'line',
                    label: 'Users',
                    data: chartData.day.users || [],
                    yAxisID: 'y1',
                    borderColor: '#0d9488',
                    borderWidth: 2.5,
                    pointRadius: 2,
                    pointHoverRadius: 4,
                    pointBackgroundColor: '#0d9488',
                    tension: 0.35,
                    fill: false,
                    order: 0
                },
                {
                    type: 'line',
                    label: 'Complete ITR',
                    data: chartData.day.completed || [],
                    yAxisID: 'y1',
                    borderColor: '#16a34a',
                    borderWidth: 2.5,
                    pointRadius: 2,
                    pointHoverRadius: 4,
                    pointBackgroundColor: '#16a34a',
                    tension: 0.35,
                    fill: false,
                    order: 0
                },
                {
                    type: 'bar',
                    label: 'Revenue',
                    data: chartData.day.revenue,
                    yAxisID: 'y',
                    backgroundColor: function (ctx) {
                        var chart = ctx.chart;
                        var c = chart.ctx, area = chart.chartArea;
                        if (!area) return 'rgba(91, 44, 255, .55)';
                        var g = c.createLinearGradient(0, area.bottom, 0, area.top);
                        g.addColorStop(0, 'rgba(91, 44, 255, .35)');
                        g.addColorStop(1, 'rgba(91, 44, 255, .85)');
                        return g;
                    },
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 36,
                    order: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 8,
                        boxHeight: 8,
                        padding: 14,
                        font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' },
                        color: '#6b7280',
                        generateLabels: function (chart) {
                            return chart.data.datasets.map(function (ds, i) {
                                var meta = chart.getDatasetMeta(i);
                                var fill = '#5b2cff';
                                if (ds.label === 'Payments') fill = '#ff5c35';
                                else if (ds.label === 'Orders') fill = '#2563eb';
                                else if (ds.label === 'Users') fill = '#0d9488';
                                else if (ds.label === 'Complete ITR') fill = '#16a34a';
                                else if (ds.label === 'Revenue') fill = '#5b2cff';
                                else if (typeof ds.borderColor === 'string') fill = ds.borderColor;
                                return {
                                    text: ds.label,
                                    fillStyle: fill,
                                    strokeStyle: fill,
                                    lineWidth: 0,
                                    hidden: !!(meta && meta.hidden),
                                    datasetIndex: i,
                                    pointStyle: 'circle',
                                    rotation: 0
                                };
                            });
                        }
                    }
                },
                tooltip: {
                    backgroundColor: '#18141f',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function (ctx) {
                            if (ctx.dataset.label === 'Revenue') return ' Revenue: ' + money(ctx.parsed.y);
                            if (ctx.dataset.label === 'Orders') return ' Orders: ' + ctx.parsed.y;
                            if (ctx.dataset.label === 'Users') return ' Users: ' + ctx.parsed.y;
                            if (ctx.dataset.label === 'Complete ITR') return ' Complete ITR: ' + ctx.parsed.y;
                            return ' Payments: ' + ctx.parsed.y;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' },
                        color: '#6b7280',
                        maxRotation: 0,
                        maxTicksLimit: 10
                    }
                },
                y: {
                    position: 'left',
                    beginAtZero: true,
                    grid: { color: 'rgba(231, 228, 239, .9)' },
                    ticks: {
                        font: { family: 'Plus Jakarta Sans', size: 11 },
                        color: '#6b7280',
                        callback: function (v) {
                            if (v >= 100000) return '₹' + (v / 100000).toFixed(1) + 'L';
                            if (v >= 1000) return '₹' + (v / 1000).toFixed(0) + 'k';
                            return '₹' + v;
                        }
                    }
                },
                y1: {
                    position: 'right',
                    beginAtZero: true,
                    grid: { drawOnChartArea: false },
                    ticks: {
                        precision: 0,
                        font: { family: 'Plus Jakarta Sans', size: 11 },
                        color: '#ff5c35'
                    }
                }
            },
            animation: { duration: 650, easing: 'easeOutQuart' }
        }
    });

    updateAside('day');

    document.querySelectorAll('.itr-admin-chart-card .itr-admin-chart-tabs button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var period = btn.getAttribute('data-period');
            var set = chartData[period];
            if (!set) return;
            document.querySelectorAll('.itr-admin-chart-card .itr-admin-chart-tabs button').forEach(function (b) {
                b.classList.toggle('is-active', b === btn);
                b.setAttribute('aria-selected', b === btn ? 'true' : 'false');
            });
            chart.data.labels = set.labels;
            chart.data.datasets[0].data = set.count;
            chart.data.datasets[1].data = set.orders || [];
            chart.data.datasets[2].data = set.users || [];
            chart.data.datasets[3].data = set.completed || [];
            chart.data.datasets[4].data = set.revenue;
            chart.update();
            updateAside(period);
        });
    });
})();
</script>
@endpush
