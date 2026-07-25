<?php use App\Core\Helper; ?>
<div class="itr-panel-hero">
    <div>
        <h1>Assigned clients</h1>
        <p>Review filings, request docs, add notes and mark returns filed.</p>
    </div>
</div>
<div class="itr-card"><div class="itr-table-wrap"><table>
<tr><th>Client</th><th>Plan</th><th>ITR</th><th>Status</th><th></th></tr>
<?php if (!$clients): ?><tr><td colspan="5" class="itr-empty">No clients assigned yet.</td></tr><?php endif; ?>
<?php foreach ($clients as $c): ?>
<tr>
    <td>
        <strong><?= Helper::e($c['client_name']) ?></strong>
        <div class="itr-help"><?= Helper::e($c['client_email'] ?? '') ?></div>
    </td>
    <td><?= Helper::e($c['plan_name'] ?? '-') ?></td>
    <td><?= Helper::e($c['itr_type'] ?? '-') ?></td>
    <td><?= Helper::statusBadge($c['status']) ?></td>
    <td><a class="itr-btn itr-btn-outline itr-btn-sm" href="/ca/filings/<?= (int)$c['id'] ?>">Open</a></td>
</tr>
<?php endforeach; ?>
</table></div></div>
