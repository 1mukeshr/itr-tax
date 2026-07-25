<?php use App\Core\Helper; ?>
<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Free tool</span>
        <h1>Income Tax Calculator</h1>
        <p>Estimate tax under old vs new regime for FY <?= Helper::e($app['financial_year']) ?> — illustrative demo only, not a legal computation.</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container">
<div class="itr-calc-grid">
    <div class="itr-card">
        <div class="itr-card-h"><?= Helper::icon('chart') ?> Your income details</div>
        <div class="itr-card-b">
            <div class="itr-form-group">
                <label>Gross total income (₹)</label>
                <input class="itr-form-control" type="number" id="calcGross" value="900000" min="0" step="1000">
            </div>
            <div class="itr-form-group">
                <label>Deductions 80C/80D etc. (₹) — old regime</label>
                <input class="itr-form-control" type="number" id="calcDeduct" value="150000" min="0" step="1000">
            </div>
            <button class="itr-btn itr-btn-primary" type="button" id="calcBtn"><?= Helper::icon('spark') ?> Calculate tax</button>
            <p class="itr-help itr-mt-sm">Uses the same demo slab logic as the filing Tax Summary step.</p>
        </div>
    </div>
    <div class="itr-card">
        <div class="itr-card-h"><?= Helper::icon('rupee') ?> Results</div>
        <div class="itr-card-b">
            <div class="itr-grid-2">
                <div class="itr-box">
                    <div class="itr-help">Old regime tax</div>
                    <div class="itr-price itr-price-md" id="calcOld">₹0.00</div>
                </div>
                <div class="itr-box">
                    <div class="itr-help">New regime tax</div>
                    <div class="itr-price itr-price-md" id="calcNew">₹0.00</div>
                </div>
            </div>
            <div class="itr-alert itr-alert-success itr-mt-md" id="calcRec">Enter income and calculate.</div>
            <div class="itr-actions-row">
                <a class="itr-btn itr-btn-orange" href="/register"><?= Helper::icon('spark') ?> Start Filing</a>
                <a class="itr-btn itr-btn-outline" href="/pricing"><?= Helper::icon('users') ?> Hire Expert</a>
            </div>
        </div>
    </div>
</div>
</div></section>
