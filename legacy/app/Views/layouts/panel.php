<?php
use App\Core\Helper;
use App\Core\Auth;
$role = Auth::role();
$menu = match ($role) {
    'admin' => [
        'Main' => [['Dashboard', '/admin']],
        'Manage' => [
            ['Users', '/admin/users'], ['CA Management', '/admin/cas'],
            ['Orders', '/admin/orders'], ['Payments', '/admin/payments'], ['Coupons', '/admin/coupons'],
        ],
        'Content' => [
            ['Blogs', '/admin/blogs'], ['FAQs', '/admin/faqs'],
            ['Settings', '/admin/settings'],
        ],
    ],
    'ca' => [
        'Work' => [['Dashboard', '/ca'], ['Assigned Clients', '/ca/clients']],
    ],
    default => [
        'Filing' => [
            ['Dashboard', '/dashboard'], ['Start Filing', '/itr/new'],
            ['Track Status', '/track'], ['Profile', '/profile'],
        ],
    ],
};
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Helper::e(($title ?? 'Panel') . ' | ' . $app['name']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/assets/css/itr-tax.css">
</head>
<body class="itr-tax">
<div class="itr-wrap">
    <aside class="itr-side">
        <a class="itr-logo" href="/"><span><?= Helper::icon('logo') ?></span><?= Helper::e($app['name']) ?></a>
        <?php foreach ($menu as $label => $items): ?>
            <div class="label"><?= Helper::e($label) ?></div>
            <?php foreach ($items as [$name, $href]): ?>
                <?php $on = $path === $href || ($href !== '/admin' && $href !== '/ca' && $href !== '/dashboard' && str_starts_with($path, $href)); ?>
                <a class="<?= $on ? 'itr-active' : '' ?>" href="<?= $href ?>"><?= Helper::e($name) ?></a>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <div class="label">Account</div>
        <a href="/logout">Logout</a>
    </aside>
    <div class="itr-main">
        <div class="itr-top">
            <div>
                <strong><?= Helper::e($title ?? '') ?></strong>
                <div class="itr-help">FY <?= Helper::e($app['financial_year']) ?> · AY <?= Helper::e($app['assessment_year']) ?></div>
            </div>
            <div class="itr-text-right">
                <strong><?= Helper::e($authUser['name'] ?? '') ?></strong>
                <div class="itr-help"><?= Helper::e(strtoupper($role ?? '')) ?></div>
            </div>
        </div>
        <div class="itr-content">
            <?php if (!empty($flash)): ?>
                <div class="itr-alert itr-alert-<?= $flash['type'] === 'error' ? 'error' : ($flash['type'] === 'info' ? 'info' : 'success') ?>">
                    <?= Helper::e($flash['message']) ?>
                </div>
            <?php endif; ?>
            <?= $content ?>
        </div>
    </div>
</div>
<script src="/assets/js/itr-tax.js"></script>
</body>
</html>
