<?php

/**
 * Create MySQL/MariaDB database for ITR Tax.
 * Usage: php -c php.ini tools/setup-mysql.php
 */
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_DATABASE') ?: 'itr_tax';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';

// Load .env if present
$envFile = dirname(__DIR__).DIRECTORY_SEPARATOR.'.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || ! str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
        $k = trim($k);
        $v = trim($v, " \t\"'");
        match ($k) {
            'DB_HOST' => $host = $v,
            'DB_PORT' => $port = $v,
            'DB_DATABASE' => $dbName = $v,
            'DB_USERNAME' => $user = $v,
            'DB_PASSWORD' => $pass = $v,
            default => null,
        };
    }
}

if (! extension_loaded('pdo_mysql')) {
    fwrite(STDERR, "ERROR: pdo_mysql extension is not loaded. Enable it in php.ini.\n");
    exit(1);
}

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $safe = preg_replace('/[^a-zA-Z0-9_]/', '', $dbName) ?: 'itr_tax';
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safe}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "OK: Database `{$safe}` is ready on {$host}:{$port}\n";
    echo "Next: php artisan migrate:fresh --seed\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'ERROR: '.$e->getMessage()."\n");
    fwrite(STDERR, "Make sure MySQL/MariaDB is running and .env DB_* credentials are correct.\n");
    exit(1);
}
