<?php
use App\Core\Helper;
$oldPrices = ['Basic' => 5000, 'Standard' => 7598, 'Premium' => 11998];
?>
<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">FY <?= Helper::e($app['financial_year']) ?> · AY <?= Helper::e($app['assessment_year']) ?></span>
        <h1>Transparent pricing for every taxpayer</h1>
        <p>File free on Self mode, or choose an assisted plan with dedicated CA support, accuracy checks and 24-hour filing SLA.</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container">
<div class="itr-section-title">
    <h2>Self vs Hire an Expert</h2>
    <p>Pick the mode that matches how complex your income is this year.</p>
</div>
<div class="itr-table-wrap itr-mb-md">
<table class="itr-compare-table">
    <tr>
        <th>What’s included</th>
        <th>Self Filing</th>
        <th>Hire Expert</th>
    </tr>
    <tr>
        <td>Form 16 / AIS / 26AS upload</td>
        <td class="itr-ok-mark"><?= Helper::icon('check') ?></td>
        <td class="itr-ok-mark"><?= Helper::icon('check') ?></td>
    </tr>
    <tr>
        <td>Tax summary &amp; old vs new regime</td>
        <td class="itr-ok-mark"><?= Helper::icon('check') ?></td>
        <td class="itr-ok-mark"><?= Helper::icon('check') ?></td>
    </tr>
    <tr>
        <td>Dedicated CA review</td>
        <td class="itr-no-mark">—</td>
        <td class="itr-ok-mark"><?= Helper::icon('check') ?></td>
    </tr>
    <tr>
        <td>Capital gains / F&amp;O / ESOP support</td>
        <td class="itr-no-mark">Limited</td>
        <td class="itr-ok-mark"><?= Helper::icon('check') ?></td>
    </tr>
    <tr>
        <td>Filing + acknowledgement tracking</td>
        <td class="itr-ok-mark"><?= Helper::icon('check') ?></td>
        <td class="itr-ok-mark"><?= Helper::icon('check') ?></td>
    </tr>
    <tr>
        <td>Typical turnaround</td>
        <td>Same day (self)</td>
        <td>Within 24 hours*</td>
    </tr>
</table>
</div>
<p class="itr-text-center itr-help">*After documents and payment are complete. Demo SLA for product showcase.</p>
</div></section>

<section class="itr-section itr-alt"><div class="itr-container">
<div class="itr-section-title">
    <h2>Assisted Filing Plans</h2>
    <p>India’s tax experts one click away — priced clearly, billed once per return.</p>
</div>
<div class="itr-grid-3">
<?php foreach ($plans as $i => $plan): $features = json_decode($plan['features'] ?? '[]', true) ?: []; $old = $oldPrices[$plan['name']] ?? null; ?>
<div class="itr-plan <?= $i === 1 ? 'itr-hot' : '' ?>">
    <?php if ($i === 1): ?><span class="itr-tag">Most Popular</span>
    <?php elseif ($i === 0): ?><span class="itr-tag itr-tag-orange">Starter</span>
    <?php else: ?><span class="itr-tag">Premium</span><?php endif; ?>
    <h3 class="itr-plan-name"><?= Helper::e($plan['name']) ?></h3>
    <div class="itr-price"><?= Helper::money($plan['price']) ?><?php if ($old): ?><span class="itr-old"><?= Helper::money($old) ?></span><?php endif; ?></div>
    <p><?= Helper::e($plan['description']) ?></p>
    <ul><?php foreach ($features as $f): ?><li><?= Helper::icon('check') ?> <?= Helper::e($f) ?></li><?php endforeach; ?></ul>
    <a class="itr-btn <?= $i === 1 ? 'itr-btn-primary' : 'itr-btn-outline' ?> itr-btn-block" href="/register">Get started</a>
</div>
<?php endforeach; ?>
</div>

<div class="itr-soft-note itr-mt-lg">
    Tip: Use coupon <strong>SAVE10</strong> for 10% off (min ₹999) or <strong>FLAT500</strong> for ₹500 off on plans from ₹2,499.
</div>

<div class="itr-cta-band itr-mt-lg">
    <h2>Not sure which plan fits?</h2>
    <p>Start with Self Filing for simple Form 16 cases. Upgrade to an expert anytime if capital gains, F&amp;O or foreign income appear.</p>
    <div class="itr-cta-actions">
        <a class="itr-btn itr-btn-orange" href="/register">Start Filing Now</a>
        <a class="itr-btn itr-btn-white" href="/contact">Talk to support</a>
    </div>
</div>
</div></section>

<section class="itr-section"><div class="itr-container">
<div class="itr-section-title">
    <h2>What’s included in every assisted return</h2>
</div>
<div class="itr-feature-row">
    <div class="itr-feature-mini"><?= Helper::iconBox('list') ?><h3>Document checklist</h3><p>Clear list of Form 16, AIS, proofs and statements you need.</p></div>
    <div class="itr-feature-mini"><?= Helper::iconBox('users') ?><h3>Expert match</h3><p>Automatic CA assignment after successful payment.</p></div>
    <div class="itr-feature-mini"><?= Helper::iconBox('message') ?><h3>Review notes</h3><p>CA comments, doc requests and status updates in one place.</p></div>
    <div class="itr-feature-mini"><?= Helper::iconBox('download') ?><h3>ACK download</h3><p>Download acknowledgement once your return is filed.</p></div>
</div>
</div></section>
