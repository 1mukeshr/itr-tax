<?php

$base = dirname(__DIR__).'/resources/views';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));

foreach ($iterator as $file) {
    if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
        continue;
    }

    $path = $file->getPathname();
    $content = file_get_contents($path);
    $original = $content;

    $fixes = [
        "/->id'\]/" => '->id',
        "/->status'\]/" => '->status',
        "/->filing_mode'\]/" => '->filing_mode',
        "/->plan_name'\]/" => '->plan_name',
        "/->name'\]/" => '->name',
        "/->email'\]/" => '->email',
        "/->title'\]/" => '->title',
        "/->slug'\]/" => '->slug',
        "/->amount'\]/" => '->amount',
        "/->created_at'\]/" => '->created_at',
        "/->tax_regime'\]/" => '->tax_regime',
        "/->gross_salary'\]/" => '->gross_salary',
        "/->total_deductions'\]/" => '->total_deductions',
        "/->tax_old_regime'\]/" => '->tax_old_regime',
        "/->tax_new_regime'\]/" => '->tax_new_regime',
        "/->acknowledgement_no'\]/" => '->acknowledgement_no',
        "/->original_name'\]/" => '->original_name',
        "/->doc_type'\]/" => '->doc_type',
        "/->message'\]/" => '->message',
        "/->required_docs'\]/" => '->required_docs',
        "/->note'\]/" => '->note',
        "/->author_name'\]/" => '->author_name',
        "/->changed_by_name'\]/" => '->changed_by_name',
        "/->new_status'\]/" => '->new_status',
        "/->remark'\]/" => '->remark',
        "/->client_name'\]/" => '->client_name',
        "/->question'\]/" => '->question',
        "/->answer'\]/" => '->answer',
        "/->excerpt'\]/" => '->excerpt',
        "/->content'\]/" => '->content',
        "/->price'\]/" => '->price',
        "/->description'\]/" => '->description',
        "/\\$r\\['message'\\]/" => '$r->message',
        "/\\$r\\['required_docs'\\]/" => '$r->required_docs',
        '/@endauth/' => '@endif',
        "/<\?php\\s*use App\\\\Core\\\\Helper;\\s*use App\\\\Core\\\\View;\\s*\\?>/" => '',
        "/<\?php View::partial\\('partials\\/steps', \\['filing' => \\$filing\\]\\); \\?>/" => "@include('partials.steps', ['filing' => \$filing])",
        "/<\?php View::partial\\('partials\\/steps', \\['filing' => \\$filing, 'step' => \\$step\\]\\); \\?>/" => "@include('partials.steps', ['filing' => \$filing])",
        "/<\?php if \\(!\\$filings\\): \\?>/" => '@if($filings->isEmpty())',
        "/<\?php if \\(\\\$requests\\): \\?>/" => '@if($requests->isNotEmpty())',
        "/<\?php if \\(\\\$docs\\): \\?>/" => '@if($docs->isNotEmpty())',
        "/<\?php if \\(\\\$ca\\): \\?>/" => '@if($ca)',
        "/<\?php if \\(\\\$receipt\\): \\?>/" => '@if($receipt)',
        "/<\?php if \\(in_array\\(\\\$filing->status, \\[([^\\]]+)\\], true\\)\\): \\?>/" => '@if(in_array($filing->status, [$1], true))',
        "/<\?php foreach \\(\\\$docTypes as \\$k => \\$label\\): \\?>/" => '@foreach($docTypes as $k => $label)',
        "/<\?php foreach \\(\\\$requests as \\$r\\): \\?>/" => '@foreach($requests as $r)',
        "/<\?php foreach \\(\\\$docs as \\$doc\\): \\?>/" => '@foreach($docs as $doc)',
        "/<\?php foreach \\(\\\$logs as \\$log\\): \\?>/" => '@foreach($logs as $log)',
        "/<\?php foreach \\(\\\$notes as \\$note\\): \\?>/" => '@foreach($notes as $note)',
        "/<\?php foreach \\(\\\$clients as \\$c\\): \\?>/" => '@foreach($clients as $c)',
        "/<\?php foreach \\(\\\$users as \\$u\\): \\?>/" => '@foreach($users as $u)',
        "/<\?php foreach \\(\\\$orders as \\$o\\): \\?>/" => '@foreach($orders as $o)',
        "/<\?php foreach \\(\\\$payments as \\$p\\): \\?>/" => '@foreach($payments as $p)',
        "/<\?php foreach \\(\\\$coupons as \\$c\\): \\?>/" => '@foreach($coupons as $c)',
        "/<\?php foreach \\(\\\$notifications as \\$n\\): \\?>/" => '@foreach($notifications as $n)',
        "/<\?php else: \\?>/" => '@else',
        "/<\?php endif; \\?>/" => '@endif',
        "/<\?= \\(int\\)\\$stats\\['([^']+)'\\] \\?>/" => '{{ (int) $stats[\'$1\'] }}',
        "/<\?= \\(int\\)\\$f->id \\?>/" => '{{ $f->id }}',
        "/<\?= \\(int\\)\\$filing->id \\?>/" => '{{ $filing->id }}',
        "/<\?= \\$filing->filing_mode === 'self' \\? 'Self Filing' : 'Hire Expert' \\?>/" => "{{ \$filing->filing_mode === 'self' ? 'Self Filing' : 'Hire Expert' }}",
        "/<\?= \\$f->filing_mode === 'self' \\? 'Self' : \\{\\{ \\$f->plan_name \\?\\? 'Expert' \\}\\} \\?>/" => "{{ \$f->filing_mode === 'self' ? 'Self' : (\$f->plan->name ?? 'Expert') }}",
        "/action=\"\\{\\{ url\\('\\/documents\\/\\<\\?= \\(int\\)\\$filing->id \\?\\>\\'\\) \\}\\}\"/" => 'action="{{ route(\'user.upload-document\', $filing) }}"',
        "/href=\"\\{\\{ url\\('\\/summary\\/\\<\\?= \\(int\\)\\$filing->id \\?\\>\\'\\) \\}\\}\"/" => 'href="{{ route(\'user.summary\', $filing) }}"',
        "/href=\"\\{\\{ url\\('\\/track\\/\\<\\?= \\(int\\)\\$f->id \\?\\>\\'\\) \\}\\}\"/" => 'href="{{ route(\'user.track\', $f) }}"',
    ];

    foreach ($fixes as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }

    // Remove remaining short php tags
    $content = preg_replace('/<\?php[^?]*\?>/', '', $content);
    $content = preg_replace('/<\?= ([^?]+) \?>/', '{{ $1 }}', $content);

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo 'Fixed '.str_replace($base.'\\', '', $path)."\n";
    }
}
