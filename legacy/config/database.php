<?php

return [
    'driver' => 'sqlite',
    'sqlite' => [
        'path' => __DIR__ . '/../database/itr-tax.sqlite',
    ],
    'mysql' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'itr-tax',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
];
