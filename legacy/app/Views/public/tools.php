<?php use App\Core\Helper; ?>
<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">ClearTax-style tools</span>
        <h1>Tax tools that make filing easier</h1>
        <p>Calculators and helpers for FY <?= Helper::e($app['financial_year']) ?> — free to explore before you file.</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container">
<div class="itr-grid-3">
    <a class="itr-tool-card" href="/tax-calculator">
        <?= Helper::iconBox('chart') ?>
        <h3>Income Tax Calculator</h3>
        <p>Compare old vs new regime tax payable instantly.</p>
        <span class="itr-link-more">Open tool <?= Helper::icon('arrow-right') ?></span>
    </a>
    <a class="itr-tool-card" href="/refund-status">
        <?= Helper::iconBox('rupee') ?>
        <h3>Refund Status</h3>
        <p>Demo checker for acknowledgement / refund tracking tips.</p>
        <span class="itr-link-more">Check status <?= Helper::icon('arrow-right') ?></span>
    </a>
    <a class="itr-tool-card" href="/how-it-works">
        <?= Helper::iconBox('list') ?>
        <h3>Filing journey map</h3>
        <p>See Self vs Expert steps before you start.</p>
        <span class="itr-link-more">View journey <?= Helper::icon('arrow-right') ?></span>
    </a>
    <a class="itr-tool-card" href="/blogs/old-vs-new-tax-regime">
        <?= Helper::iconBox('spark') ?>
        <h3>Regime guide</h3>
        <p>Understand which regime may give maximum refund.</p>
        <span class="itr-link-more">Read guide <?= Helper::icon('arrow-right') ?></span>
    </a>
    <a class="itr-tool-card" href="/blogs/form-16-vs-form-26as">
        <?= Helper::iconBox('file') ?>
        <h3>Form 16 vs 26AS</h3>
        <p>Checklist to avoid TDS mismatches.</p>
        <span class="itr-link-more">Read guide <?= Helper::icon('arrow-right') ?></span>
    </a>
    <a class="itr-tool-card" href="/pricing">
        <?= Helper::iconBox('users') ?>
        <h3>Expert plan finder</h3>
        <p>Basic, Standard or Premium — pick based on complexity.</p>
        <span class="itr-link-more">View plans <?= Helper::icon('arrow-right') ?></span>
    </a>
</div>
</div></section>
