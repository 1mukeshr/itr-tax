<?php
use App\Core\Helper;
use App\Core\View;
?>
<div class="itr-page-title">
    <h1>Track Filing #<?= (int)$filing['id'] ?></h1>
    <p><?= Helper::statusBadge($filing['status']) ?> · <?= $filing['filing_mode'] === 'self' ? 'Self Filing' : 'Expert Assisted' ?> · <?= Helper::e($filing['itr_type']) ?></p>
</div>
<?php View::partial('partials/steps', ['filing' => $filing]); ?>

<div class="itr-grid-2">
<div class="itr-card"><div class="itr-card-h">Timeline</div><div class="itr-card-b">
<div class="itr-timeline">
<?php if (!$logs): ?><div class="itr-empty">No updates yet.</div><?php endif; ?>
<?php foreach ($logs as $log): ?>
<div class="itr-tl">
    <strong><?= Helper::e(Helper::statusLabel($log['new_status'])) ?></strong>
    <div class="itr-help"><?= Helper::e($log['remark'] ?? '') ?> · <?= Helper::formatDate($log['created_at'], 'd M Y, h:i A') ?></div>
</div>
<?php endforeach; ?>
</div>
</div></div>
<div>
<div class="itr-card"><div class="itr-card-b">
    <h3><?= $filing['filing_mode'] === 'self' ? 'Self filing' : 'Assigned Expert' ?></h3>
    <?php if ($filing['filing_mode'] === 'assisted'): ?>
        <?php if ($ca): ?>
            <p class="itr-text-ink"><strong><?= Helper::e($ca['name']) ?></strong></p>
            <div class="itr-help"><?= Helper::e($ca['email']) ?> · ITR filed in 24hrs target</div>
        <?php else: ?><p>Expert will be assigned after payment.</p><?php endif; ?>
    <?php else: ?>
        <p>You are filing yourself. E-verify after ACK is generated.</p>
    <?php endif; ?>
    <div class="itr-actions-row">
        <a class="itr-btn itr-btn-outline itr-btn-sm" href="/documents/<?= (int)$filing['id'] ?>">Docs</a>
        <a class="itr-btn itr-btn-outline itr-btn-sm" href="/summary/<?= (int)$filing['id'] ?>">Summary</a>
        <?php if ($filing['filing_mode'] === 'self'): ?>
            <a class="itr-btn itr-btn-outline itr-btn-sm" href="/review/<?= (int)$filing['id'] ?>">Review</a>
        <?php else: ?>
            <a class="itr-btn itr-btn-outline itr-btn-sm" href="/payment/<?= (int)$filing['id'] ?>">Payment</a>
        <?php endif; ?>
        <a class="itr-btn itr-btn-outline itr-btn-sm" href="/acknowledgement/<?= (int)$filing['id'] ?>">ACK</a>
    </div>
</div></div>
<div class="itr-card"><div class="itr-card-h">Notes from expert</div><div class="itr-card-b">
<?php if (!$notes): ?><div class="itr-empty">No notes yet.</div><?php endif; ?>
<?php foreach ($notes as $n): ?>
    <p class="itr-text-ink"><?= Helper::e($n['note']) ?></p>
    <div class="itr-help"><?= Helper::e($n['author_name']) ?></div>
<?php endforeach; ?>
</div></div>
</div>
</div>
