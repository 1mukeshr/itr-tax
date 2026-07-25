<?php use App\Core\Helper; ?>
<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Oops</span>
        <h1>Page not found</h1>
        <p>The link may be outdated, or the page moved. Let’s get you back to filing.</p>
    </div>
</div></div>
<section class="itr-section"><div class="itr-container">
<div class="itr-empty-state">
    <?= Helper::iconBox('search') ?>
    <h3>We couldn’t find that page</h3>
    <p>Try the homepage, pricing, or jump straight into your dashboard if you’re logged in.</p>
    <div class="itr-gap-row itr-gap-row-center">
        <a class="itr-btn itr-btn-primary" href="/"><?= Helper::icon('home') ?> Go home</a>
        <a class="itr-btn itr-btn-outline" href="/pricing"><?= Helper::icon('rupee') ?> View pricing</a>
        <a class="itr-btn itr-btn-orange" href="/register"><?= Helper::icon('spark') ?> Start Filing</a>
    </div>
</div>
</div></section>
