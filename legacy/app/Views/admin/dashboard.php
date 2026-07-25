<?php use App\Core\Helper; ?>
<div class="itr-welcome">
    <h2>Admin control centre</h2>
    <p>Monitor users, CA capacity, orders and payments for FY <?= Helper::e($app['financial_year']) ?>.</p>
    <div class="itr-welcome-actions">
        <a class="itr-btn itr-btn-white" href="/admin/orders">Manage orders</a>
        <a class="itr-btn itr-btn-orange" href="/admin/cas">CA management</a>
    </div>
</div>
<div class="itr-stats">
    <div class="itr-stat itr-stat-accent"><div class="l">Users</div><div class="v"><?= (int)$stats['users'] ?></div></div>
    <div class="itr-stat"><div class="l">Active CAs</div><div class="v"><?= (int)$stats['cas'] ?></div></div>
    <div class="itr-stat"><div class="l">Open filings</div><div class="v"><?= (int)$stats['pending'] ?></div></div>
    <div class="itr-stat"><div class="l">Revenue</div><div class="v itr-v-sm"><?= Helper::money($stats['revenue']) ?></div></div>
</div>
<div class="itr-grid-2">
<div class="itr-card"><div class="itr-card-h">Recent orders <a href="/admin/orders">All</a></div>
<div class="itr-table-wrap"><table>
<tr><th>Client</th><th>Plan</th><th>Status</th></tr>
<?php if (!$recentOrders): ?><tr><td colspan="3" class="itr-empty">No orders yet.</td></tr><?php endif; ?>
<?php foreach ($recentOrders as $o): ?>
<tr><td><?= Helper::e($o['client_name']) ?></td><td><?= Helper::e($o['plan_name'] ?? '-') ?></td><td><?= Helper::statusBadge($o['status']) ?></td></tr>
<?php endforeach; ?>
</table></div></div>
<div class="itr-card"><div class="itr-card-h">Recent payments <a href="/admin/payments">All</a></div>
<div class="itr-table-wrap"><table>
<tr><th>User</th><th>Amount</th><th>Txn</th></tr>
<?php if (!$recentPayments): ?><tr><td colspan="3" class="itr-empty">No payments yet.</td></tr><?php endif; ?>
<?php foreach ($recentPayments as $p): ?>
<tr><td><?= Helper::e($p['user_name']) ?></td><td><?= Helper::money($p['amount']) ?></td><td><?= Helper::e($p['transaction_id']) ?></td></tr>
<?php endforeach; ?>
</table></div></div>
</div>
