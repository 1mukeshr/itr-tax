<?php

$base = dirname(__DIR__).'/resources/views';

$public = ['efiling', 'pricing', 'how-it-works', 'tax-calculator', 'tools', 'refund-status', 'about', 'privacy', 'terms', 'blogs', 'blog-show', 'faqs', 'contact'];
$user = ['dashboard', 'start-filing', 'documents', 'summary', 'review', 'payment', 'track-list', 'track', 'acknowledgement', 'profile'];
$ca = ['dashboard', 'clients', 'filing'];
$admin = ['dashboard', 'users', 'cas', 'ca-form', 'orders', 'payments', 'coupons', 'blogs', 'faqs', 'settings'];
$auth = ['login', 'register'];

function wrap(string $path, string $layout, string $title): void
{
    if (! file_exists($path)) {
        echo "Skip missing $path\n";

        return;
    }
    $content = file_get_contents($path);
    if (str_contains($content, '@extends')) {
        echo "Already wrapped $path\n";

        return;
    }
    $wrapped = "@extends('$layout')\n\n@section('title', '$title')\n\n@section('content')\n".$content."\n@endsection\n";
    file_put_contents($path, $wrapped);
    echo "Wrapped $path\n";
}

foreach ($public as $v) {
    wrap("$base/public/$v.blade.php", 'layouts.main', ucwords(str_replace('-', ' ', $v)));
}
foreach ($user as $v) {
    wrap("$base/user/$v.blade.php", 'layouts.panel', ucwords(str_replace('-', ' ', $v)));
}
foreach ($ca as $v) {
    wrap("$base/ca/$v.blade.php", 'layouts.panel', ucwords(str_replace('-', ' ', $v)));
}
foreach ($admin as $v) {
    wrap("$base/admin/$v.blade.php", 'layouts.panel', ucwords(str_replace('-', ' ', $v)));
}
foreach ($auth as $v) {
    wrap("$base/auth/$v.blade.php", 'layouts.auth', ucfirst($v));
}

// home-content stays as partial included by home.blade.php
// partials/steps - no wrap
// errors/404 - wrap with main
wrap("$base/errors/404.blade.php", 'layouts.main', 'Not Found');
