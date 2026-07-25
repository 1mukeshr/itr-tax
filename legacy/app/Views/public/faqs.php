<?php use App\Core\Helper; ?>
<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Help Centre</span>
        <h1>Frequently Asked Questions</h1>
        <p>Everything you need to know about e-filing on <?= Helper::e($app['name']) ?> — documents, regimes, expert plans and refunds.</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container itr-container-narrow">
<div class="itr-faq-cats">
    <span class="itr-faq-cat itr-active">All</span>
    <span class="itr-faq-cat">General</span>
    <span class="itr-faq-cat">Process</span>
    <span class="itr-faq-cat">Documents</span>
    <span class="itr-faq-cat">Refund</span>
</div>

<?php if (!$faqs): ?>
<div class="itr-empty-state">
    <?= Helper::iconBox('help') ?>
    <h3>No FAQs yet</h3>
    <p>Check back soon, or write to support if you need help right away.</p>
    <a class="itr-btn itr-btn-primary" href="/contact"><?= Helper::icon('mail') ?> Contact support</a>
</div>
<?php endif; ?>

<?php foreach ($faqs as $faq): ?>
<details class="itr-faq" data-faq-cat="<?= Helper::e($faq['category'] ?? 'General') ?>">
    <summary><?= Helper::e($faq['question']) ?></summary>
    <p><?= nl2br(Helper::e($faq['answer'])) ?></p>
</details>
<?php endforeach; ?>

<div class="itr-cta-band itr-mt-lg">
    <h2>Still have a question?</h2>
    <p>Our support team helps with documents, payments, expert matching and filing status.</p>
    <div class="itr-cta-actions">
        <a class="itr-btn itr-btn-orange" href="/contact"><?= Helper::icon('mail') ?> Write to support</a>
        <a class="itr-btn itr-btn-white" href="/register"><?= Helper::icon('spark') ?> Start Filing</a>
    </div>
</div>
</div></section>
