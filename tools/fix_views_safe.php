<?php

$base = dirname(__DIR__).'/resources/views';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));

foreach ($iterator as $file) {
    if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
        continue;
    }

    $path = $file->getPathname();
    $c = file_get_contents($path);

    $c = preg_replace('/\$app->(\w+)/', "\$app['$1']", $c);
    $c = preg_replace('/\$stats->(\w+)/', "\$stats['$1']", $c);
    $c = preg_replace('/\$settings->(\w+)/', "\$settings['$1']", $c);
    $c = str_replace('{{ @csrf }}', '@csrf', $c);
    $c = str_replace('$s->label', "\$s['label']", $c);
    $c = str_replace("href=\"{{ url('/itr/new') }}\"", 'href="{{ route(\'user.start-filing\') }}"', $c);
    $c = str_replace("href=\"{{ url('/track') }}\"", 'href="{{ route(\'user.track-list\') }}"', $c);
    $c = str_replace("href=\"{{ url('/profile') }}\"", 'href="{{ route(\'user.profile\') }}"', $c);

    file_put_contents($path, $c);
}

echo "Fixed views\n";
