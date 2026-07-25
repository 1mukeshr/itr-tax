<?php use App\Core\Helper; ?>
<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Company</span>
        <h1>About <?= Helper::e($app['name']) ?></h1>
        <p>A ClearTax-inspired income-tax e-filing experience — built to make Form 16 → summary → file (or hire expert) simple, accurate and transparent.</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container">
<div class="itr-grid-2">
    <div class="itr-box">
        <h2>Our mission</h2>
        <p>Help every Indian taxpayer file with clarity — maximum refund focus, regime comparison, and expert help when income gets complex.</p>
        <ul class="itr-check-list">
            <li><?= Helper::icon('check') ?> Self Filing for salaried Form 16 cases</li>
            <li><?= Helper::icon('check') ?> Expert plans for investors, traders &amp; NRIs</li>
            <li><?= Helper::icon('check') ?> Secure vault, CA workspace &amp; admin controls</li>
        </ul>
    </div>
    <div class="itr-box">
        <h2>What this demo showcases</h2>
        <p>Full product flow inspired by ClearTax: marketing site, auth, user filing journey, CA review desk and admin operations.</p>
        <div class="itr-grid-2 itr-mt-md">
            <div class="itr-feature-mini"><?= Helper::iconBox('spark') ?><h3>Product</h3><p>eFiling UX</p></div>
            <div class="itr-feature-mini"><?= Helper::iconBox('shield') ?><h3>Trust</h3><p>Role security</p></div>
        </div>
    </div>
</div>
<div class="itr-cta-band itr-mt-lg">
    <h2>Ready to experience the filing flow?</h2>
    <p>Create an account and start with Form 16 in minutes.</p>
    <div class="itr-cta-actions">
        <a class="itr-btn itr-btn-orange" href="/register"><?= Helper::icon('spark') ?> Start Filing</a>
        <a class="itr-btn itr-btn-white" href="/contact"><?= Helper::icon('mail') ?> Contact us</a>
    </div>
</div>
</div></section>
