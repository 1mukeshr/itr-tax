<?php use App\Core\Helper; ?>
<div class="itr-page-title"><h1>Payments</h1></div>
<div class="itr-card"><div class="itr-table-wrap"><table>
<tr><th>Txn</th><th>User</th><th>Amount</th><th>Coupon</th><th>Status</th><th>Date</th></tr>
<?php foreach ($payments as $p): ?>
<tr>
    <td><?= Helper::e($p['transaction_id']) ?></td>
    <td><?= Helper::e($p['user_name']) ?></td>
    <td><?= Helper::money($p['amount']) ?></td>
    <td><?= Helper::e($p['coupon_code'] ?? '-') ?></td>
    <td><?= Helper::statusBadge($p['status'] === 'success' ? 'completed' : 'payment_pending') ?></td>
    <td><?= Helper::formatDate($p['paid_at'] ?? $p['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table></div></div>
