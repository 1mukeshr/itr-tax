<?php use App\Core\Helper; ?>
<div class="itr-welcome">
    <h2>CA workspace</h2>
    <p>Review assigned clients, request documents, add notes and mark returns as filed.</p>
    <div class="itr-welcome-actions">
        <a class="itr-btn itr-btn-white" href="/ca/clients">Open client list</a>
    </div>
</div>
<div class="itr-stats">
    <div class="itr-stat itr-stat-accent"><div class="l">Assigned</div><div class="v"><?= (int)$stats['assigned'] ?></div></div>
    <div class="itr-stat"><div class="l">In review</div><div class="v"><?= (int)$stats['review'] ?></div></div>
    <div class="itr-stat"><div class="l">Filed</div><div class="v"><?= (int)$stats['filed'] ?></div></div>
    <div class="itr-stat"><div class="l">Doc requests</div><div class="v"><?= (int)$stats['pending_docs'] ?></div></div>
</div>
<div class="itr-card"><div class="itr-card-h">Recent clients <a href="/ca/clients">All</a></div>
<div class="itr-table-wrap"><table>
<tr><th>Client</th><th>Plan</th><th>Status</th><th></th></tr>
<?php foreach ($clients as $c): ?>
<tr>
    <td><?= Helper::e($c['client_name']) ?></td>
    <td><?= Helper::e($c['plan_name'] ?? '-') ?></td>
    <td><?= Helper::statusBadge($c['status']) ?></td>
    <td><a href="/ca/filings/<?= (int)$c['id'] ?>">Open</a></td>
</tr>
<?php endforeach; ?>
<?php if (!$clients): ?><tr><td colspan="4" class="itr-empty">No clients assigned yet — new expert filings appear here after payment.</td></tr><?php endif; ?>
</table></div></div>
