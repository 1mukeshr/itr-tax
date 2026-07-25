<?php
use App\Core\Helper;
use App\Core\View;
$tax = ($filing['tax_regime'] ?? 'new') === 'old' ? $filing['tax_old_regime'] : $filing['tax_new_regime'];
?>
<div class="itr-page-title">
    <h1>Review &amp; File</h1>
    <p>Confirm details and file your ITR with 100% accuracy focus — ClearTax-style final step.</p>
</div>
<?php View::partial('partials/steps', ['filing' => $filing]); ?>

<div class="itr-card"><div class="itr-card-h"><?= Helper::icon('file') ?> ITR preview</div><div class="itr-card-b">
<div class="itr-grid-2">
    <div><div class="itr-help">Mode</div><strong>Self Filing</strong></div>
    <div><div class="itr-help">Profile</div><strong><?= Helper::e(ucwords(str_replace('_', ' ', $filing['income_profile'] ?? 'salaried'))) ?></strong></div>
    <div><div class="itr-help">ITR Type</div><strong><?= Helper::e($filing['itr_type']) ?></strong></div>
    <div><div class="itr-help">PAN</div><strong><?= Helper::e($filing['pan'] ?? '-') ?></strong></div>
    <div><div class="itr-help">Gross income</div><strong><?= Helper::money($filing['gross_salary']) ?></strong></div>
    <div><div class="itr-help">Selected regime</div><strong><?= strtoupper($filing['tax_regime'] ?? 'new') ?></strong></div>
    <div><div class="itr-help">Estimated tax (demo)</div><strong><?= Helper::money($tax) ?></strong></div>
    <div><div class="itr-help">AY</div><strong><?= Helper::e($filing['assessment_year']) ?></strong></div>
</div>
</div></div>

<?php if (in_array($filing['status'], ['ready_to_file', 'summary_pending'], true)): ?>
<form method="post" action="/review/<?= (int)$filing['id'] ?>/file">
    <?= Helper::csrfField() ?>
    <div class="itr-alert itr-alert-info"><?= Helper::icon('shield') ?> After filing, e-verify within 120 days on the Income Tax portal (Aadhaar OTP / net banking).</div>
    <div class="itr-actions-row">
        <button class="itr-btn itr-btn-orange" type="submit"><?= Helper::icon('check-circle') ?> File ITR Now</button>
        <a class="itr-btn itr-btn-outline" href="/summary/<?= (int)$filing['id'] ?>">Edit summary</a>
    </div>
</form>
<?php else: ?>
    <div class="itr-alert itr-alert-success">Already filed. <?= Helper::statusBadge($filing['status']) ?></div>
    <a class="itr-btn itr-btn-primary" href="/acknowledgement/<?= (int)$filing['id'] ?>"><?= Helper::icon('download') ?> View Acknowledgement</a>
<?php endif; ?>
