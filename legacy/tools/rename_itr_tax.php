<?php
/**
 * Rename project classes to itr-* prefix + brand to ITR Tax
 * Run: php -c php.ini tools/rename_itr_tax.php
 */

$root = dirname(__DIR__);

$classMap = [
    // longest first
    'btn-hero-outline' => 'itr-btn-hero-outline',
    'btn-primary' => 'itr-btn-primary',
    'btn-orange' => 'itr-btn-orange',
    'btn-outline' => 'itr-btn-outline',
    'btn-danger' => 'itr-btn-danger',
    'btn-white' => 'itr-btn-white',
    'btn-block' => 'itr-btn-block',
    'btn-sm' => 'itr-btn-sm',
    'badge-success' => 'itr-badge-success',
    'badge-danger' => 'itr-badge-danger',
    'badge-muted' => 'itr-badge-muted',
    'badge-warn' => 'itr-badge-warn',
    'badge-info' => 'itr-badge-info',
    'alert-success' => 'itr-alert-success',
    'alert-error' => 'itr-alert-error',
    'alert-info' => 'itr-alert-info',
    'tag-orange' => 'itr-tag-orange',
    'page-title-row' => 'itr-page-title-row',
    'page-title' => 'itr-page-title',
    'page-banner' => 'itr-page-banner',
    'section-title' => 'itr-section-title',
    'section-clarity' => 'itr-section-clarity',
    'section-plans' => 'itr-section-plans',
    'section' => 'itr-section',
    'header-inner' => 'itr-header-inner',
    'header' => 'itr-header',
    'hero-actions' => 'itr-hero-actions',
    'hero-eyebrow' => 'itr-hero-eyebrow',
    'hero-stats' => 'itr-hero-stats',
    'hero-stat' => 'itr-hero-stat',
    'hero-card' => 'itr-hero-card',
    'hero-copy' => 'itr-hero-copy',
    'hero-grid' => 'itr-hero-grid',
    'hero-note' => 'itr-hero-note',
    'hero' => 'itr-hero',
    'trust-row' => 'itr-trust-row',
    'trust-pill' => 'itr-trust-pill',
    'mode-tabs' => 'itr-mode-tabs',
    'chip-row' => 'itr-chip-row',
    'check-list' => 'itr-check-list',
    'check-inline' => 'itr-check-inline',
    'dropzone-card' => 'itr-dropzone-card',
    'dropzone' => 'itr-dropzone',
    'clarity-info' => 'itr-clarity-info',
    'clarity-grid' => 'itr-clarity-grid',
    'process-cards' => 'itr-process-cards',
    'process-card' => 'itr-process-card',
    'process-num' => 'itr-process-num',
    'cta-band' => 'itr-cta-band',
    'cta-actions' => 'itr-cta-actions',
    'why-item' => 'itr-why-item',
    'audience' => 'itr-audience',
    'plan-name' => 'itr-plan-name',
    'plan-split' => 'itr-plan-split',
    'is-clickable' => 'itr-is-clickable',
    'form-group' => 'itr-form-group',
    'form-control' => 'itr-form-control',
    'form-row' => 'itr-form-row',
    'table-wrap' => 'itr-table-wrap',
    'card-h' => 'itr-card-h',
    'card-b' => 'itr-card-b',
    'card' => 'itr-card',
    'grid-3' => 'itr-grid-3',
    'grid-2' => 'itr-grid-2',
    'grid-4' => 'itr-grid-4',
    'container-narrow' => 'itr-container-narrow',
    'container-article' => 'itr-container-article',
    'container-form' => 'itr-container-form',
    'container' => 'itr-container',
    'footer-grid' => 'itr-footer-grid',
    'footer-copy' => 'itr-footer-copy',
    'footer-desc' => 'itr-footer-desc',
    'footer' => 'itr-footer',
    'logo-light' => 'itr-logo-light',
    'auth-page' => 'itr-auth-page',
    'auth-box' => 'itr-auth-box',
    'auth-logo' => 'itr-auth-logo',
    'auth-foot' => 'itr-auth-foot',
    'demo-box' => 'itr-demo-box',
    'flash-wrap' => 'itr-flash-wrap',
    'side' => 'itr-side',
    'wrap' => 'itr-wrap',
    'main' => 'itr-main',
    'top' => 'itr-top',
    'content' => 'itr-content',
    'stats' => 'itr-stats',
    'stat' => 'itr-stat',
    'tabs' => 'itr-tabs',
    'timeline' => 'itr-timeline',
    'step-item' => 'itr-step-item',
    'step-line' => 'itr-step-line',
    'steps' => 'itr-steps',
    'step' => 'itr-step',
    'menu' => 'itr-menu',
    'logo' => 'itr-logo',
    'btn' => 'itr-btn',
    'badge' => 'itr-badge',
    'alert' => 'itr-alert',
    'tag' => 'itr-tag',
    'plan' => 'itr-plan',
    'box' => 'itr-box',
    'chip' => 'itr-chip',
    'faq' => 'itr-faq',
    'empty' => 'itr-empty',
    'help' => 'itr-help',
    'lead' => 'itr-lead',
    'price-lg' => 'itr-price-lg',
    'price-md' => 'itr-price-md',
    'price' => 'itr-price',
    'old' => 'itr-old',
    'text-center' => 'itr-text-center',
    'text-right' => 'itr-text-right',
    'text-ink' => 'itr-text-ink',
    'mt-sm' => 'itr-mt-sm',
    'mt-md' => 'itr-mt-md',
    'mt-lg' => 'itr-mt-lg',
    'mb-sm' => 'itr-mb-sm',
    'mb-md' => 'itr-mb-md',
    'gap-row' => 'itr-gap-row',
    'inline-form' => 'itr-inline-form',
    'search-form' => 'itr-search-form',
    'search-input' => 'itr-search-input',
    'select-sm' => 'itr-select-sm',
    'pan-input' => 'itr-pan-input',
    'cursor-pointer' => 'itr-cursor-pointer',
    'stack-form' => 'itr-stack-form',
    'notify-item' => 'itr-notify-item',
    'note-block' => 'itr-note-block',
    'list-row' => 'itr-list-row',
    'list-item' => 'itr-list-item',
    'link-more' => 'itr-link-more',
    'title-spaced' => 'itr-title-spaced',
    'actions-row' => 'itr-actions-row',
    'article-lead' => 'itr-article-lead',
    'article-body' => 'itr-article-body',
    'back-link' => 'itr-back-link',
    'regime-pick' => 'itr-regime-pick',
    'hidden' => 'itr-hidden',
    'hot' => 'itr-hot',
    'active' => 'itr-active',
    'done' => 'itr-done',
    'alt' => 'itr-alt',
    'v-sm' => 'itr-v-sm',
    'ico' => 'itr-ico',
    'tl' => 'itr-tl',
];

uksort($classMap, fn($a, $b) => strlen($b) <=> strlen($a));

$varMap = [
    '--purple-dark' => '--itr-purple-dark',
    '--purple-soft' => '--itr-purple-soft',
    '--purple-mid' => '--itr-purple-mid',
    '--purple' => '--itr-purple',
    '--orange-dark' => '--itr-orange-dark',
    '--orange' => '--itr-orange',
    '--white' => '--itr-white',
    '--muted' => '--itr-muted',
    '--text' => '--itr-text',
    '--line' => '--itr-line',
    '--ok' => '--itr-ok',
    '--warn' => '--itr-warn',
    '--err' => '--itr-err',
    '--radius' => '--itr-radius',
    '--shadow-sm' => '--itr-shadow-sm',
    '--shadow' => '--itr-shadow',
    '--display' => '--itr-display',
    '--font' => '--itr-font',
    '--bg' => '--itr-bg',
];
uksort($varMap, fn($a, $b) => strlen($b) <=> strlen($a));

function replaceClasses(string $content, array $classMap): string
{
    // CSS selectors .class
    foreach ($classMap as $from => $to) {
        $content = preg_replace('/\.' . preg_quote($from, '/') . '(?![a-zA-Z0-9_-])/', '.' . $to, $content);
    }
    // HTML class="..." attributes — replace tokens
    $content = preg_replace_callback('/\bclass=(["\'])(.*?)\1/s', function ($m) use ($classMap) {
        $quote = $m[1];
        $classes = preg_split('/\s+/', trim($m[2]));
        $classes = array_map(function ($c) use ($classMap) {
            return $classMap[$c] ?? $c;
        }, $classes);
        return 'class=' . $quote . implode(' ', $classes) . $quote;
    }, $content);
    // JS querySelector '.class'
    foreach ($classMap as $from => $to) {
        $content = str_replace("'.$from'", "'.$to'", $content);
        $content = str_replace('".' . $from . '"', '".' . $to . '"', $content);
        $content = str_replace('.' . $from . '[', '.' . $to . '[', $content);
    }
    return $content;
}

function replaceVars(string $content, array $varMap): string
{
    foreach ($varMap as $from => $to) {
        $content = str_replace($from, $to, $content);
    }
    return $content;
}

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$count = 0;
foreach ($files as $file) {
    if (!$file->isFile()) continue;
    $path = $file->getPathname();
    if (str_contains($path, DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR)) continue;
    if (str_contains($path, 'database' . DIRECTORY_SEPARATOR . 'taxease')) continue;
    $ext = strtolower($file->getExtension());
    if (!in_array($ext, ['css', 'php', 'js', 'md'], true)) continue;

    $before = file_get_contents($path);
    $after = replaceClasses($before, $classMap);
    if ($ext === 'css' || $ext === 'php') {
        $after = replaceVars($after, $varMap);
    }

    // brand renames
    $after = str_replace(
        ['ClearFile', 'clearfile.in', 'clearfile', 'TaxEase', 'taxease.in', 'taxease'],
        ['ITR Tax', 'itr-tax.in', 'itr-tax', 'ITR Tax', 'itr-tax.in', 'itr-tax'],
        $after
    );

    if ($after !== $before) {
        file_put_contents($path, $after);
        $count++;
        echo "Updated: " . str_replace($root . DIRECTORY_SEPARATOR, '', $path) . "\n";
    }
}

echo "Done. Files updated: {$count}\n";
