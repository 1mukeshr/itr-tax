<?php
use App\Core\Helper;
use App\Core\View;
$saving = abs((float)$filing['tax_old_regime'] - (float)$filing['tax_new_regime']);
$better = ((float)$filing['tax_new_regime'] <= (float)$filing['tax_old_regime']) ? 'new' : 'old';
?>
<div class="itr-page-title">
    <h1>Tax Summary</h1>
    <p>Every number computed clearly — compare regimes and pick the option with lower tax payable.</p>
</div>
<?php View::partial('partials/steps', ['filing' => $filing]); ?>

<form method="post" action="/summary/<?= (int)$filing['id'] ?>">
<?= Helper::csrfField() ?>
<div class="itr-grid-2">
<div class="itr-card"><div class="itr-card-h">Income details (from Form 16)</div><div class="itr-card-b">
    <div class="itr-form-group">
        <label>Gross salary / total income (₹)</label>
        <input class="itr-form-control" type="number" step="1" name="gross_salary" value="<?= Helper::e($filing['gross_salary'] ?: 900000) ?>" required>
    </div>
    <div class="itr-form-group">
        <label>Deductions 80C/80D etc. (₹) — old regime</label>
        <input class="itr-form-control" type="number" step="1" name="total_deductions" value="<?= Helper::e($filing['total_deductions'] ?: 150000) ?>" required>
    </div>
    <div class="itr-soft-note">Demo calculator — illustrative only, not a legal tax computation. Adjust figures to match your Form 16.</div>
</div></div>

<div class="itr-card"><div class="itr-card-h">Old vs New regime</div><div class="itr-card-b">
    <p class="itr-help itr-mb-sm">Select the regime you want to file under. We highlight the lower tax payable.</p>
    <div class="itr-grid-2">
        <label class="itr-box itr-regime-pick <?= ($filing['tax_regime'] ?? '') === 'old' ? 'itr-hot' : '' ?>">
            <input type="radio" name="tax_regime" value="old" <?= ($filing['tax_regime'] ?? '') === 'old' ? 'checked' : '' ?>>
            <strong>Old Regime</strong>
            <div class="itr-price itr-price-md"><?= Helper::money($filing['tax_old_regime']) ?></div>
            <div class="itr-help">With deductions (demo)</div>
        </label>
        <label class="itr-box itr-regime-pick <?= ($filing['tax_regime'] ?? 'new') !== 'old' ? 'itr-hot' : '' ?>">
            <input type="radio" name="tax_regime" value="new" <?= ($filing['tax_regime'] ?? 'new') !== 'old' ? 'checked' : '' ?>>
            <strong>New Regime</strong>
            <div class="itr-price itr-price-md"><?= Helper::money($filing['tax_new_regime']) ?></div>
            <div class="itr-help">Lower slabs, fewer deductions</div>
        </label>
    </div>
    <div class="itr-alert itr-alert-success itr-mt-md">
        Recommended: <strong><?= strtoupper($better) ?> regime</strong> — you may save about <?= Helper::money($saving) ?>.
    </div>
</div></div>
</div>

<div class="itr-actions-row">
<button class="itr-btn itr-btn-primary" type="submit">
    <?= $filing['filing_mode'] === 'self' ? 'Continue to Review & File' : 'Continue to Payment' ?>
</button>
<a class="itr-btn itr-btn-outline" href="/documents/<?= (int)$filing['id'] ?>">Back to documents</a>
</div>
</form>
