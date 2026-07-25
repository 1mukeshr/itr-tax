<?php
use App\Core\Helper;
use App\Core\View;
?>
<div class="itr-page-title">
    <h1>Acknowledgement</h1>
    <p>Filing #<?= (int)$filing['id'] ?> · <?= Helper::statusBadge($filing['status']) ?></p>
</div>
<?php View::partial('partials/steps', ['filing' => $filing]); ?>

<div class="itr-card"><div class="itr-card-b">
<?php if ($filing['acknowledgement_no']): ?>
    <div class="itr-dual-top">
        <?= Helper::iconBox('check-circle') ?>
        <div>
            <h3>ACK No: <?= Helper::e($filing['acknowledgement_no']) ?></h3>
            <p>Filed on <?= Helper::formatDate($filing['filed_at'], 'd M Y, h:i A') ?> · Regime: <?= strtoupper($filing['tax_regime'] ?? 'new') ?></p>
        </div>
    </div>
    <div class="itr-alert itr-alert-info">E-verify within <strong>120 days</strong> using Aadhaar OTP or net banking on the Income Tax portal.</div>
<?php else: ?>
    <div class="itr-empty-state">
        <?= Helper::iconBox('clock') ?>
        <h3>ITR not filed yet</h3>
        <p><?= $filing['filing_mode'] === 'self' ? 'Complete Review & File to generate acknowledgement.' : 'Your expert will file and share ACK here.' ?></p>
    </div>
<?php endif; ?>

<?php if ($receipt): ?>
    <a class="itr-btn itr-btn-primary" href="/acknowledgement/<?= (int)$filing['id'] ?>/download"><?= Helper::icon('download') ?> Download Acknowledgement</a>
<?php elseif ($filing['acknowledgement_no']): ?>
    <div class="itr-alert itr-alert-info itr-mt-md">Acknowledgement number is ready. File download appears when receipt is uploaded.</div>
<?php endif; ?>
</div></div>
