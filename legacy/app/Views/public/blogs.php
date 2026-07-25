<?php use App\Core\Helper; ?>
<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Knowledge Hub</span>
        <h1>ITR Guides &amp; Resources</h1>
        <p>Practical guides for FY <?= Helper::e($app['financial_year']) ?> — regimes, Form 16 checks, AIS mismatches and filing tips.</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container">
<?php if (!$blogs): ?>
<div class="itr-empty-state">
    <?= Helper::iconBox('pen') ?>
    <h3>Guides coming soon</h3>
    <p>We’re publishing filing tips for this assessment year. Meanwhile, start your return or browse FAQs.</p>
    <div class="itr-gap-row itr-gap-row-center">
        <a class="itr-btn itr-btn-orange" href="/register"><?= Helper::icon('spark') ?> Start Filing</a>
        <a class="itr-btn itr-btn-outline" href="/faqs"><?= Helper::icon('help') ?> Read FAQs</a>
    </div>
</div>
<?php else: ?>
<div class="itr-grid-3">
<?php foreach ($blogs as $i => $blog): ?>
<article class="itr-blog-card">
    <div class="itr-blog-cover"><span><?= Helper::icon('pen') ?> Guide</span></div>
    <div class="itr-blog-body">
        <div class="itr-blog-meta"><?= Helper::formatDate($blog['published_at']) ?> · <?= Helper::e($blog['author_name'] ?? 'ITR Tax') ?></div>
        <h3><a href="/blogs/<?= Helper::e($blog['slug']) ?>"><?= Helper::e($blog['title']) ?></a></h3>
        <p><?= Helper::e($blog['excerpt']) ?></p>
        <a class="itr-link-more" href="/blogs/<?= Helper::e($blog['slug']) ?>">Read guide <?= Helper::icon('arrow-right') ?></a>
    </div>
</article>
<?php endforeach; ?>
</div>
<?php endif; ?>

<div class="itr-cta-band itr-mt-lg">
    <h2>Ready to put the guide into action?</h2>
    <p>Upload Form 16 and get a clear tax summary for AY <?= Helper::e($app['assessment_year']) ?>.</p>
    <div class="itr-cta-actions">
        <a class="itr-btn itr-btn-orange" href="/register"><?= Helper::icon('spark') ?> Start Filing Now</a>
        <a class="itr-btn itr-btn-white" href="/how-it-works"><?= Helper::icon('list') ?> See how it works</a>
    </div>
</div>
</div></section>
