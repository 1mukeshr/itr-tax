<?php use App\Core\Helper; ?>
<div class="itr-welcome">
    <h2>Welcome back<?= !empty($authUser['name']) ? ', ' . Helper::e(explode(' ', $authUser['name'])[0]) : '' ?></h2>
    <p>FY <?= Helper::e($app['financial_year']) ?> · AY <?= Helper::e($app['assessment_year']) ?> — continue a return or start a new filing with Form 16.</p>
    <div class="itr-welcome-actions">
        <a class="itr-btn itr-btn-orange" href="/itr/new"><?= Helper::icon('spark') ?> Start Filing</a>
        <a class="itr-btn itr-btn-white" href="/track"><?= Helper::icon('list') ?> Track status</a>
    </div>
</div>

<div class="itr-stats">
    <div class="itr-stat itr-stat-accent"><div class="l">Total filings</div><div class="v"><?= (int)$stats['total'] ?></div></div>
    <div class="itr-stat"><div class="l">In progress</div><div class="v"><?= (int)$stats['active'] ?></div></div>
    <div class="itr-stat"><div class="l">Filed / completed</div><div class="v"><?= (int)$stats['filed'] ?></div></div>
    <div class="itr-stat"><div class="l">Notifications</div><div class="v"><?= (int)$stats['notifications'] ?></div></div>
</div>

<div class="itr-grid-2">
<div class="itr-card">
    <div class="itr-card-h">Recent filings <a href="/track">View all</a></div>
    <?php if (!$filings): ?>
    <div class="itr-card-b">
        <div class="itr-empty-state">
            <?= Helper::iconBox('plus') ?>
            <h3>No filings yet</h3>
            <p>Start with Self Filing for Form 16, or Hire an Expert for capital gains and complex income.</p>
            <a class="itr-btn itr-btn-orange" href="/itr/new"><?= Helper::icon('spark') ?> Start Filing</a>
        </div>
    </div>
    <?php else: ?>
    <div class="itr-table-wrap"><table>
        <tr><th>ID</th><th>Mode</th><th>Status</th><th></th></tr>
        <?php foreach ($filings as $f): ?>
        <tr>
            <td>#<?= (int)$f['id'] ?></td>
            <td><?= $f['filing_mode'] === 'self' ? 'Self' : Helper::e($f['plan_name'] ?? 'Expert') ?></td>
            <td><?= Helper::statusBadge($f['status']) ?></td>
            <td><a href="/track/<?= (int)$f['id'] ?>">Open</a></td>
        </tr>
        <?php endforeach; ?>
    </table></div>
    <?php endif; ?>
</div>
<div class="itr-card">
    <div class="itr-card-h">Notifications</div>
    <div class="itr-card-b">
        <?php if (!$notifications): ?>
            <div class="itr-empty">You’re all caught up — no new alerts.</div>
        <?php endif; ?>
        <?php foreach ($notifications as $n): ?>
        <div class="itr-notify-item">
            <strong><?= Helper::e($n['title']) ?></strong>
            <div class="itr-help"><?= Helper::e($n['message']) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</div>

<div class="itr-card itr-mt-md">
    <div class="itr-card-h">Filing tips for this year</div>
    <div class="itr-card-b">
        <ul class="itr-tip-list">
            <li><?= Helper::icon('check') ?> Match Form 16 TDS with AIS / 26AS before you file to avoid refund delays.</li>
            <li><?= Helper::icon('chart') ?> Compare old vs new regime on the Tax Summary step — pick the lower tax payable.</li>
            <li><?= Helper::icon('shield') ?> E-verify within 120 days of filing using Aadhaar OTP or net banking.</li>
        </ul>
    </div>
</div>
