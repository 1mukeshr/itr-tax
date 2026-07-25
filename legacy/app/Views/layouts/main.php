<?php use App\Core\Helper; use App\Core\Auth; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Helper::e(($title ?? 'Income Tax eFiling') . ' | ' . $app['name']) ?></title>
    <meta name="description" content="File ITR online for FY <?= Helper::e($app['financial_year']) ?> (AY <?= Helper::e($app['assessment_year']) ?>) with 100% accuracy. Self filing or hire a tax expert — ClearTax-style e-filing on ITR Tax.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/assets/css/itr-tax.css">
</head>
<body class="itr-tax">
<header class="itr-header">
    <div class="itr-container itr-header-inner">
        <a class="itr-logo" href="/"><span><?= Helper::icon('logo') ?></span><?= Helper::e($app['name']) ?></a>
        <button class="itr-nav-toggle" type="button" aria-label="Open menu" data-nav-toggle>
            <?= Helper::icon('menu') ?>
        </button>
        <nav class="itr-menu" data-nav-menu>
            <a href="/efiling">eFiling</a>
            <a href="/how-it-works">How it works</a>
            <a href="/pricing">Pricing</a>
            <a href="/tax-calculator">Tax Calculator</a>
            <a href="/tools">Tools</a>
            <a href="/blogs">Guides</a>
            <a href="/faqs">FAQs</a>
            <?php if (Auth::check()): ?>
                <?php $dash = match (Auth::role()) { 'admin' => '/admin', 'ca' => '/ca', default => '/dashboard' }; ?>
                <a class="itr-btn itr-btn-primary itr-btn-sm" href="<?= $dash ?>">Dashboard</a>
            <?php else: ?>
                <a href="/login">Login</a>
                <a class="itr-btn itr-btn-orange itr-btn-sm" href="/register">Start Filing</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<?php if (!empty($flash)): ?>
<div class="itr-container itr-flash-wrap">
    <div class="itr-alert itr-alert-<?= $flash['type'] === 'error' ? 'error' : ($flash['type'] === 'info' ? 'info' : 'success') ?>">
        <?= Helper::e($flash['message']) ?>
    </div>
</div>
<?php endif; ?>

<?= $content ?>

<footer class="itr-footer">
    <div class="itr-container itr-footer-grid itr-footer-mega">
        <div>
            <div class="itr-logo itr-logo-light"><span><?= Helper::icon('logo') ?></span><?= Helper::e($app['name']) ?></div>
            <p class="itr-footer-desc">File ITR in minutes with 100% accuracy for FY <?= Helper::e($app['financial_year']) ?> (AY <?= Helper::e($app['assessment_year']) ?>). Maximum tax refund — self filing or hire an expert.</p>
            <div class="itr-footer-trust">
                <span><?= Helper::icon('shield') ?> Secure</span>
                <span><?= Helper::icon('check-circle') ?> Accurate</span>
                <span><?= Helper::icon('clock') ?> 24×7 help</span>
            </div>
        </div>
        <div>
            <h4>Income Tax</h4>
            <p><a href="/efiling">eFiling overview</a></p>
            <p><a href="/itr/new">Start Filing</a></p>
            <p><a href="/pricing">Hire an Expert</a></p>
            <p><a href="/how-it-works">How it works</a></p>
            <p><a href="/refund-status">Refund status</a></p>
        </div>
        <div>
            <h4>Tools &amp; Guides</h4>
            <p><a href="/tax-calculator">Tax Calculator</a></p>
            <p><a href="/tools">All tax tools</a></p>
            <p><a href="/blogs">ITR guides</a></p>
            <p><a href="/faqs">FAQs</a></p>
            <p><a href="/blogs/old-vs-new-tax-regime">Old vs New regime</a></p>
        </div>
        <div>
            <h4>Company</h4>
            <p><a href="/about">About us</a></p>
            <p><a href="/contact">Support</a></p>
            <p><a href="/privacy">Privacy policy</a></p>
            <p><a href="/terms">Terms of use</a></p>
            <p>support@itr-tax.in</p>
            <p>+91 98765 43210</p>
        </div>
    </div>
    <div class="itr-container itr-footer-copy">
        © <?= date('Y') ?> <?= Helper::e($app['name']) ?>. Demo ClearTax-style ITR portal — not affiliated with the Income Tax Department of India.
    </div>
</footer>
<script src="/assets/js/itr-tax.js"></script>
</body>
</html>
