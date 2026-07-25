<?php
use App\Core\Helper;
use App\Core\View;
?>
<div class="itr-page-title">
    <h1>Secure payment</h1>
    <p>Pay once to unlock instant expert match · Filing #<?= (int)$filing['id'] ?></p>
</div>
<?php View::partial('partials/steps', ['filing' => $filing]); ?>

<div class="itr-grid-2">
<div class="itr-card"><div class="itr-card-h">Order summary</div><div class="itr-card-b">
    <h3><?= Helper::e($plan['name'] ?? 'Expert Plan') ?></h3>
    <div class="itr-price itr-price-lg"><?= Helper::money($filing['amount']) ?></div>
    <div class="itr-help">Regime: <?= strtoupper($filing['tax_regime'] ?? 'new') ?> · Profile: <?= Helper::e(ucwords(str_replace('_', ' ', $filing['income_profile'] ?? ''))) ?></div>
    <ul class="itr-tip-list">
        <li><?= Helper::icon('users') ?> Dedicated CA assigned right after successful payment</li>
        <li><?= Helper::icon('clock') ?> 24-hour filing SLA once documents are complete</li>
        <li><?= Helper::icon('rupee') ?> Coupons: SAVE10 or FLAT500 (if eligible)</li>
    </ul>
    <?php if (!in_array($filing['status'], ['payment_pending','summary_pending','documents_pending'], true)): ?>
        <div class="itr-alert itr-alert-success itr-mt-md">Payment already done.</div>
        <a class="itr-btn itr-btn-primary" href="/track/<?= (int)$filing['id'] ?>">Track expert filing</a>
    <?php else: ?>
    <form method="post" action="/payment/<?= (int)$filing['id'] ?>" class="itr-mt-md">
        <?= Helper::csrfField() ?>
        <div class="itr-form-group"><label>Coupon code</label><input class="itr-form-control" name="coupon_code" placeholder="SAVE10"></div>
        <div class="itr-form-group"><label>Payment method</label>
            <select class="itr-form-control" name="method"><option value="demo">Demo Pay (instant)</option><option value="upi">UPI</option><option value="card">Card</option></select>
        </div>
        <button class="itr-btn itr-btn-orange itr-btn-block" type="submit">Pay &amp; Assign Expert</button>
    </form>
    <?php endif; ?>
</div></div>
<div class="itr-card"><div class="itr-card-h">Payment history</div>
<div class="itr-table-wrap"><table>
<tr><th>Txn</th><th>Amount</th><th>Status</th></tr>
<?php if (!$payments): ?><tr><td colspan="3" class="itr-empty">No payments yet.</td></tr><?php endif; ?>
<?php foreach ($payments as $p): ?>
<tr><td><?= Helper::e($p['transaction_id']) ?></td><td><?= Helper::money($p['amount']) ?></td><td><?= Helper::statusBadge($p['status'] === 'success' ? 'completed' : 'payment_pending') ?></td></tr>
<?php endforeach; ?>
</table></div></div>
</div>
