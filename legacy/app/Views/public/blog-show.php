<?php use App\Core\Helper; ?>
<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">ITR Guide</span>
        <h1><?= Helper::e($blog['title']) ?></h1>
        <p><?= Helper::formatDate($blog['published_at']) ?> · <?= Helper::e($blog['author_name'] ?? 'ITR Tax Editorial') ?></p>
    </div>
</div></div>
<section class="itr-section"><div class="itr-container itr-container-article">
    <p class="itr-article-lead"><?= Helper::e($blog['excerpt']) ?></p>
    <div class="itr-article-body"><?= nl2br(Helper::e($blog['content'])) ?></div>
    <div class="itr-box itr-mt-lg">
        <h3>Next step</h3>
        <p>Apply what you learned — start a filing for FY <?= Helper::e($app['financial_year']) ?> and review your tax summary.</p>
        <div class="itr-gap-row">
            <a class="itr-btn itr-btn-orange" href="/register">Start Filing</a>
            <a class="itr-btn itr-btn-outline" href="/blogs">More guides</a>
        </div>
    </div>
    <p class="itr-back-link"><a href="/blogs">← Back to all guides</a></p>
</div></section>
