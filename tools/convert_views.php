<?php

function convertView(string $content): string
{
    $content = preg_replace('/<\?php\s*use App\\\\Core\\\\Helper;\s*(use App\\\\Core\\\\Auth;\s*)?\?>/', '', $content);
    $content = preg_replace('/<\?php\s*use App\\\\Core\\\\Helper;\s*use App\\\\Core\\\\View;\s*\?>/', '', $content);
    $content = preg_replace('/<\?php\s*\$oldPrices = \[[\s\S]*?\];\s*\?>/', '', $content);
    $content = preg_replace('/<\?php View::partial\(\'partials\/steps\', \[[^\]]+\]\);\s*\?>/', "@include('partials.steps', ['filing' => \$filing])", $content);

    // Array access to object property for common vars
    $content = preg_replace('/\$(\w+)\[\'(\w+)\'\]/', '$$1->$2', $content);

    $replacements = [
        '/<\?php if \(Auth::check\(\)\): \?>/' => '@auth',
        '/<\?php \$dash = match \(Auth::role\(\)\)[\s\S]*?\?>/' => '@php $dash = auth()->user()->isAdmin() ? route("admin.dashboard") : (auth()->user()->isCa() ? route("ca.dashboard") : route("user.dashboard")); @endphp',
        '/<\?php if \(!empty\(\$flash\)\): \?>/' => '@if(session("success") || session("error") || session("info"))',
        '/<\?php if \(!\$filings\): \?>/' => '@if($filings->isEmpty())',
        '/<\?php if \(\$requests\): \?>/' => '@if($requests->isNotEmpty())',
        '/<\?php if \(\$docs\): \?>/' => '@if($docs->isNotEmpty())',
        '/<\?php if \(\$ca\): \?>/' => '@if($ca)',
        '/<\?php if \(\$receipt\): \?>/' => '@if($receipt)',
        '/<\?php if \(\$blog\): \?>/' => '@if($blog)',
        '/<\?php if \(\$ca\): \?>/' => '@if($ca)',
        '/<\?php if \(in_array\(\$filing->status, \[([^\]]+)\], true\)\): \?>/' => '@if(in_array($filing->status, [$1], true))',
        '/<\?php if \(in_array\(\$filing->status, \[\'summary_pending\',\'payment_pending\',\'ready_to_file\'\], true\)\): \?>/' => "@if(in_array(\$filing->status, ['summary_pending','payment_pending','ready_to_file'], true))",
        '/<\?php if \(\$filing->filing_mode !== \'self\'[\s\S]*?\?>/' => '@if($filing->filing_mode !== "self")',
        '/<\?php foreach \(\$menu as \$label => \$items\): \?>/' => '@foreach($panelMenu as $label => $items)',
        '/<\?php foreach \(\$items as \[\$name, \$href\]\): \?>/' => '@foreach($items as [$name, $href])',
        '/<\?php foreach \(\$steps as \$i => \$s\): \?>/' => '@foreach(filingSteps($filing) as $i => $s)',
        '/<\?php foreach \(\$faqs as \$faq\): \?>/' => '@foreach($faqs as $faq)',
        '/<\?php foreach \(\$plans as \$plan\): \?>/' => '@foreach($plans as $plan)',
        '/<\?php foreach \(\$blogs as \$blog\): \?>/' => '@foreach($blogs as $blog)',
        '/<\?php foreach \(\$filings as \$f\): \?>/' => '@foreach($filings as $f)',
        '/<\?php foreach \(\$docs as \$doc\): \?>/' => '@foreach($docs as $doc)',
        '/<\?php foreach \(\$logs as \$log\): \?>/' => '@foreach($logs as $log)',
        '/<\?php foreach \(\$notes as \$note\): \?>/' => '@foreach($notes as $note)',
        '/<\?php foreach \(\$docTypes as \$key => \$label\): \?>/' => '@foreach($docTypes as $key => $label)',
        '/<\?php foreach \(\$docTypes as \$k => \$label\): \?>/' => '@foreach($docTypes as $k => $label)',
        '/<\?php foreach \(\$clients as \$c\): \?>/' => '@foreach($clients as $c)',
        '/<\?php foreach \(\$users as \$u\): \?>/' => '@foreach($users as $u)',
        '/<\?php foreach \(\$orders as \$o\): \?>/' => '@foreach($orders as $o)',
        '/<\?php foreach \(\$payments as \$p\): \?>/' => '@foreach($payments as $p)',
        '/<\?php foreach \(\$coupons as \$c\): \?>/' => '@foreach($coupons as $c)',
        '/<\?php foreach \(\$requests as \$r\): \?>/' => '@foreach($requests as $r)',
        '/<\?php foreach \(\$requests as \$req\): \?>/' => '@foreach($requests as $req)',
        '/<\?php foreach \(\$notifications as \$n\): \?>/' => '@foreach($notifications as $n)',
        '/<\?php foreach \(\$byStatus as \$row\): \?>/' => '@foreach($byStatus as $row)',
        '/<\?php foreach \(\$byMonth as \$row\): \?>/' => '@foreach($byMonth as $row)',
        '/<\?php foreach \(\$topCas as \$row\): \?>/' => '@foreach($topCas as $row)',
        '/<\?php foreach \(\$cas as \$ca\): \?>/' => '@foreach($cas as $caRow)',
        '/<\?php foreach \(\$recentOrders as \$o\): \?>/' => '@foreach($recentOrders as $o)',
        '/<\?php foreach \(\$recentPayments as \$p\): \?>/' => '@foreach($recentPayments as $p)',
        '/<\?php endforeach; \?>/' => '@endforeach',
        '/<\?php else: \?>/' => '@else',
        '/<\?php endif; \?>/' => '@endif',
        '/<\?php if \(\$i < count\(\$steps\) - 1\): \?>/' => '@if($i < count(filingSteps($filing)) - 1)',
        '/<\?php \$steps = Helper::filingSteps\(\$filing\); \$current = Helper::currentStepIndex\(\$filing\); \?>/' => '@php $current = currentStepIndex($filing); @endphp',
        '/Helper::csrfField\(\)/' => '@csrf',
        '/<\?= Helper::csrfField\(\) \?>/' => '@csrf',
    ];

    foreach ($replacements as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }

    $content = preg_replace('/Helper::iconBox\(([^)]+)\)/', 'iconBox($1)', $content);
    $content = preg_replace('/Helper::icon\(([^)]+)\)/', 'icon($1)', $content);
    $content = preg_replace('/Helper::money\(([^)]+)\)/', 'money($1)', $content);
    $content = preg_replace('/Helper::statusBadge\(([^)]+)\)/', 'statusBadge($1)', $content);
    $content = preg_replace('/Helper::statusLabel\(([^)]+)\)/', 'statusLabel($1)', $content);
    $content = preg_replace('/Helper::timeAgo\(([^)]+)\)/', 'timeAgo($1)', $content);
    $content = preg_replace('/Helper::formatDate\(([^)]+)\)/', 'formatDate($1)', $content);
    $content = preg_replace('/Helper::e\(([^)]+)\)/', '{{ $1 }}', $content);

    $content = preg_replace('/<\?= \{\{ ([^}]+) \}\} \?>/', '{{ $1 }}', $content);
    $content = preg_replace('/<\?= icon\(([^)]+)\) \?>/', '{!! icon($1) !!}', $content);
    $content = preg_replace('/<\?= iconBox\(([^)]+)\) \?>/', '{!! iconBox($1) !!}', $content);
    $content = preg_replace('/<\?= money\(([^)]+)\) \?>/', '{{ money($1) }}', $content);
    $content = preg_replace('/<\?= statusBadge\(([^)]+)\) \?>/', '{!! statusBadge($1) !!}', $content);
    $content = preg_replace('/<\?= statusLabel\(([^)]+)\) \?>/', '{{ statusLabel($1) }}', $content);
    $content = preg_replace('/<\?= timeAgo\(([^)]+)\) \?>/', '{{ timeAgo($1) }}', $content);
    $content = preg_replace('/<\?= formatDate\(([^)]+)\) \?>/', '{{ formatDate($1) }}', $content);
    $content = preg_replace('/<\?= \(int\)\$stats\[\'(\w+)\'\] \?>/', '{{ (int) $stats[\'$1\'] }}', $content);
    $content = preg_replace('/<\?= \(int\)(\$\w+->\w+) \?>/', '{{ (int) $1 }}', $content);
    $content = preg_replace('/<\?= \$(\w+)->(\w+) === \'([^\']+)\' \? \'([^\']+)\' : \'([^\']+)\' \?>/', "{{ \$$1->$2 === '$3' ? '$4' : '$5' }}", $content);
    $content = preg_replace('/<\?= \$(\w+)->(\w+) === \'([^\']+)\' \? \'([^\']+)\' : \{\{ \$(\w+)->(\w+) \?\? \'([^\']+)\' \}\} \?>/', "{{ \$$1->$2 === '$3' ? '$4' : (\$$5->$6 ?? '$7') }}", $content);
    $content = preg_replace('/<\?= nl2br\(\{\{ \$(\w+)->(\w+) \}\}\) \?>/', '{!! nl2br(e($1->$2)) !!}', $content);

    // Route helpers for common paths
    $routeMap = [
        '/login' => "{{ route('login') }}",
        '/register' => "{{ route('register') }}",
        '/dashboard' => "{{ route('user.dashboard') }}",
        '/itr/new' => "{{ route('user.start-filing') }}",
        '/track' => "{{ route('user.track-list') }}",
        '/profile' => "{{ route('user.profile') }}",
        '/admin' => "{{ route('admin.dashboard') }}",
        '/ca' => "{{ route('ca.dashboard') }}",
        '/contact' => "{{ route('contact') }}",
    ];

    foreach ($routeMap as $path => $blade) {
        $content = str_replace('href="{{ url(\''.$path.'\') }}"', 'href="'.$blade.'"', $content);
        $content = str_replace('action="{{ url(\''.$path.'\') }}"', 'action="'.$blade.'"', $content);
    }

    $content = preg_replace('/action="\{\{ url\(\'\/documents\/(\{\{ \(int\) \$filing->id \}\})\'\) \}\}"/', 'action="{{ route(\'user.upload-document\', $filing) }}"', $content);
    $content = preg_replace('/action="\{\{ url\(\'\/summary\/(\{\{ \(int\) \$filing->id \}\})\'\) \}\}"/', 'action="{{ route(\'user.save-summary\', $filing) }}"', $content);
    $content = preg_replace('/action="\{\{ url\(\'\/payment\/(\{\{ \(int\) \$filing->id \}\})\'\) \}\}"/', 'action="{{ route(\'user.process-payment\', $filing) }}"', $content);
    $content = preg_replace('/action="\{\{ url\(\'\/review\/(\{\{ \(int\) \$filing->id \}\})\/file\'\) \}\}"/', 'action="{{ route(\'user.self-file\', $filing) }}"', $content);
    $content = preg_replace('/href="\{\{ url\(\'\/summary\/(\{\{ \(int\) \$filing->id \}\})\'\) \}\}"/', 'href="{{ route(\'user.summary\', $filing) }}"', $content);
    $content = preg_replace('/href="\{\{ url\(\'\/track\/(\{\{ \(int\) \$f->id \}\})\'\) \}\}"/', 'href="{{ route(\'user.track\', $f) }}"', $content);
    $content = preg_replace('/href="\{\{ url\(\'\/acknowledgement\/(\{\{ \(int\) \$filing->id \}\})\'\) \}\}"/', 'href="{{ route(\'user.acknowledgement\', $filing) }}"', $content);

    $content = preg_replace('/href="\/([^"]*)"/', 'href="{{ url(\'/$1\') }}"', $content);
    $content = preg_replace('/action="\/([^"]*)"/', 'action="{{ url(\'/$1\') }}"', $content);

    // Fix plan features json_decode
    $content = str_replace('json_decode($plan->features ?? \'[]\', true)', '$plan->featuresList()', $content);

    // Fix auth user reference
    $content = str_replace('$authUser->name', 'auth()->user()->name', $content);
    $content = str_replace('!empty($authUser->name)', 'auth()->user()->name', $content);

    // Remove any remaining php blocks
    $content = preg_replace('/<\?php[^?]*\?>/s', '', $content);
    $content = preg_replace('/<\?= ([^?]+) \?>/', '{{ $1 }}', $content);

    return $content;
}

$map = [
    'public/home.php' => 'public/home-content.blade.php',
    'public/efiling.php' => 'public/efiling.blade.php',
    'public/pricing.php' => 'public/pricing.blade.php',
    'public/how-it-works.php' => 'public/how-it-works.blade.php',
    'public/tax-calculator.php' => 'public/tax-calculator.blade.php',
    'public/tools.php' => 'public/tools.blade.php',
    'public/refund-status.php' => 'public/refund-status.blade.php',
    'public/about.php' => 'public/about.blade.php',
    'public/privacy.php' => 'public/privacy.blade.php',
    'public/terms.php' => 'public/terms.blade.php',
    'public/blogs.php' => 'public/blogs.blade.php',
    'public/blog-show.php' => 'public/blog-show.blade.php',
    'public/faqs.php' => 'public/faqs.blade.php',
    'public/contact.php' => 'public/contact.blade.php',
    'auth/login.php' => 'auth/login.blade.php',
    'auth/register.php' => 'auth/register.blade.php',
    'user/dashboard.php' => 'user/dashboard.blade.php',
    'user/start-filing.php' => 'user/start-filing.blade.php',
    'user/documents.php' => 'user/documents.blade.php',
    'user/summary.php' => 'user/summary.blade.php',
    'user/review.php' => 'user/review.blade.php',
    'user/payment.php' => 'user/payment.blade.php',
    'user/track-list.php' => 'user/track-list.blade.php',
    'user/track.php' => 'user/track.blade.php',
    'user/acknowledgement.php' => 'user/acknowledgement.blade.php',
    'user/profile.php' => 'user/profile.blade.php',
    'ca/dashboard.php' => 'ca/dashboard.blade.php',
    'ca/clients.php' => 'ca/clients.blade.php',
    'ca/filing.php' => 'ca/filing.blade.php',
    'admin/dashboard.php' => 'admin/dashboard.blade.php',
    'admin/users.php' => 'admin/users.blade.php',
    'admin/cas.php' => 'admin/cas.blade.php',
    'admin/ca-form.php' => 'admin/ca-form.blade.php',
    'admin/orders.php' => 'admin/orders.blade.php',
    'admin/payments.php' => 'admin/payments.blade.php',
    'admin/coupons.php' => 'admin/coupons.blade.php',
    'admin/blogs.php' => 'admin/blogs.blade.php',
    'admin/faqs.php' => 'admin/faqs.blade.php',
    'admin/settings.php' => 'admin/settings.blade.php',
    'partials/steps.php' => 'partials/steps.blade.php',
    'errors/404.php' => 'errors/404.blade.php',
];

$base = dirname(__DIR__);

foreach ($map as $src => $dest) {
    $path = $base.'/legacy/app/Views/'.$src;
    $content = convertView(file_get_contents($path));
    $out = $base.'/resources/views/'.$dest;
    if (! is_dir(dirname($out))) {
        mkdir(dirname($out), 0777, true);
    }
    file_put_contents($out, $content);
    echo "Wrote $dest\n";
}
