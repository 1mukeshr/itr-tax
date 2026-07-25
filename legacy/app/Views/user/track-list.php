<?php use App\Core\Helper; ?>
<div class="itr-panel-hero">
    <div>
        <h1>My Filings</h1>
        <p>Track Self &amp; Expert returns for FY <?= Helper::e($app['financial_year']) ?>.</p>
    </div>
    <a class="itr-btn itr-btn-orange" href="/itr/new"><?= Helper::icon('spark') ?> Start Filing</a>
</div>
<div class="itr-card"><div class="itr-table-wrap"><table>
<tr><th>ID</th><th>Mode / Plan</th><th>ITR</th><th>Status</th><th>Actions</th></tr>
<?php if (!$filings): ?><tr><td colspan="5" class="itr-empty">No filings yet. <a href="/itr/new">Start Filing</a></td></tr><?php endif; ?>
<?php foreach ($filings as $f): ?>
<tr>
    <td>#<?= (int)$f['id'] ?></td>
    <td><?= $f['filing_mode'] === 'self' ? 'Self Filing' : Helper::e($f['plan_name'] ?? 'Expert') ?></td>
    <td><?= Helper::e($f['itr_type']) ?></td>
    <td><?= Helper::statusBadge($f['status']) ?></td>
    <td class="itr-gap-row">
        <a class="itr-btn itr-btn-outline itr-btn-sm" href="/track/<?= (int)$f['id'] ?>">Track</a>
        <a class="itr-btn itr-btn-outline itr-btn-sm" href="/documents/<?= (int)$f['id'] ?>">Docs</a>
        <a class="itr-btn itr-btn-outline itr-btn-sm" href="/acknowledgement/<?= (int)$f['id'] ?>">ACK</a>
    </td>
</tr>
<?php endforeach; ?>
</table></div></div>
