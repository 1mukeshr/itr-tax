<?php use App\Core\Helper; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Helper::e(($title ?? 'Login') . ' | ' . $app['name']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/assets/css/itr-tax.css">
</head>
<body class="itr-tax">
<div class="itr-auth-page">
    <div class="itr-auth-shell">
        <aside class="itr-auth-aside">
            <a class="itr-logo itr-logo-light itr-auth-aside-logo" href="/"><span><?= Helper::icon('logo') ?></span><?= Helper::e($app['name']) ?></a>
            <h2>File ITR with clarity &amp; confidence</h2>
            <p>Upload Form 16, compare old vs new regime, and file yourself — or let a tax expert finish it in 24 hours.</p>
            <ul>
                <li><?= Helper::icon('check') ?> Secure document vault for Form 16, AIS &amp; 26AS</li>
                <li><?= Helper::icon('check') ?> Instant tax summary with regime comparison</li>
                <li><?= Helper::icon('check') ?> Dedicated CA matching for assisted plans</li>
                <li><?= Helper::icon('check') ?> Live tracking till acknowledgement</li>
            </ul>
        </aside>
        <div class="itr-auth-main">
            <?php if (!empty($flash)): ?>
                <div class="itr-alert itr-alert-<?= $flash['type'] === 'error' ? 'error' : 'success' ?>"><?= Helper::e($flash['message']) ?></div>
            <?php endif; ?>
            <?= $content ?>
        </div>
    </div>
</div>
<script src="/assets/js/itr-tax.js"></script>
</body>
</html>
