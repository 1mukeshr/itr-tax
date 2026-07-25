<?php
use App\Core\Helper;
$oldPrices = ['Basic' => 5000, 'Standard' => 7598, 'Premium' => 11998];
?>
<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Income Tax eFiling</span>
        <h1>File your ITR online for FY <?= Helper::e($app['financial_year']) ?></h1>
        <p>AY <?= Helper::e($app['assessment_year']) ?> — Self Filing in minutes or Hire an Expert for 24-hour assisted filing with 100% accuracy focus.</p>
        <div class="itr-banner-actions">
            <a class="itr-btn itr-btn-orange" href="/register"><?= Helper::icon('spark') ?> Start Filing Now</a>
            <a class="itr-btn itr-btn-white" href="/pricing"><?= Helper::icon('users') ?> Hire an Expert</a>
        </div>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container">
<div class="itr-section-title"><h2>Why use <?= Helper::e($app['name']) ?> to file?</h2></div>
<div class="itr-feature-grid">
    <div class="itr-feat"><?= Helper::iconBox('upload') ?><h3>Zero friction Form 16 upload</h3><p>Start with Form 16 — build summary without messy spreadsheets.</p></div>
    <div class="itr-feat"><?= Helper::iconBox('chart') ?><h3>Right regime, clearly shown</h3><p>Old vs new tax payable side-by-side before you commit.</p></div>
    <div class="itr-feat"><?= Helper::iconBox('list') ?><h3>AIS / 26AS reconciliation</h3><p>Upload statements and avoid common TDS mismatch delays.</p></div>
    <div class="itr-feat"><?= Helper::iconBox('file') ?><h3>ITR-1 to ITR-4 support</h3><p>Pick the form that matches salary, gains, business or presumptive income.</p></div>
    <div class="itr-feat"><?= Helper::iconBox('users') ?><h3>Expert desk in 24 hrs</h3><p>Assisted plans include CA review, notes and acknowledgement.</p></div>
    <div class="itr-feat"><?= Helper::iconBox('download') ?><h3>ACK + e-verify reminder</h3><p>Download acknowledgement and e-verify within 120 days.</p></div>
</div>
</div></section>

<section class="itr-section itr-alt"><div class="itr-container">
<div class="itr-section-title"><h2>File ITR in 3 simple steps</h2></div>
<div class="itr-grid-3">
    <div class="itr-box itr-process-card"><span class="itr-process-num">01</span><h3>Upload documents</h3><p>Form 16, AIS, 26AS, proofs.</p></div>
    <div class="itr-box itr-process-card"><span class="itr-process-num">02</span><h3>Review tax summary</h3><p>Compare regimes &amp; deductions.</p></div>
    <div class="itr-box itr-process-card"><span class="itr-process-num">03</span><h3>File or hire expert</h3><p>Self-file or pay for CA filing.</p></div>
</div>
</div></section>

<section class="itr-section"><div class="itr-container">
<div class="itr-section-title"><h2>Assisted plans</h2></div>
<div class="itr-grid-3">
<?php foreach ($plans as $i => $plan): $features = json_decode($plan['features'] ?? '[]', true) ?: []; ?>
<div class="itr-plan <?= $i === 1 ? 'itr-hot' : '' ?>">
    <?php if ($i === 1): ?><span class="itr-tag">Popular</span><?php endif; ?>
    <h3 class="itr-plan-name"><?= Helper::e($plan['name']) ?></h3>
    <div class="itr-price"><?= Helper::money($plan['price']) ?><?php if (!empty($oldPrices[$plan['name']])): ?><span class="itr-old"><?= Helper::money($oldPrices[$plan['name']]) ?></span><?php endif; ?></div>
    <p><?= Helper::e($plan['description']) ?></p>
    <ul><?php foreach (array_slice($features, 0, 4) as $f): ?><li><?= Helper::icon('check') ?> <?= Helper::e($f) ?></li><?php endforeach; ?></ul>
    <a class="itr-btn <?= $i === 1 ? 'itr-btn-primary' : 'itr-btn-outline' ?> itr-btn-block" href="/register">Get started</a>
</div>
<?php endforeach; ?>
</div>
</div></section>

<?php if (!empty($faqs)): ?>
<section class="itr-section itr-alt"><div class="itr-container itr-container-narrow">
<div class="itr-section-title"><h2>eFiling FAQs</h2></div>
<?php foreach ($faqs as $faq): ?>
<details class="itr-faq"><summary><?= Helper::e($faq['question']) ?></summary><p><?= nl2br(Helper::e($faq['answer'])) ?></p></details>
<?php endforeach; ?>
</div></section>
<?php endif; ?>
