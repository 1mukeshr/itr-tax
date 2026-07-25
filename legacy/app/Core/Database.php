<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $config = require __DIR__ . '/../../config/database.php';
            try {
                if ($config['driver'] === 'sqlite') {
                    $path = $config['sqlite']['path'];
                    $dir = dirname($path);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0775, true);
                    }
                    self::$pdo = new PDO('sqlite:' . $path);
                    self::$pdo->exec('PRAGMA foreign_keys = ON');
                } else {
                    $m = $config['mysql'];
                    $dsn = sprintf(
                        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                        $m['host'],
                        $m['port'],
                        $m['database'],
                        $m['charset']
                    );
                    self::$pdo = new PDO($dsn, $m['username'], $m['password']);
                }
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$pdo;
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row ?: null;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function insert(string $table, array $data): int
    {
        $cols = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        self::query("INSERT INTO {$table} ({$cols}) VALUES ({$placeholders})", array_values($data));
        return (int) self::connection()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): void
    {
        $sets = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($data)));
        self::query(
            "UPDATE {$table} SET {$sets} WHERE {$where}",
            array_merge(array_values($data), $whereParams)
        );
    }

    public static function delete(string $table, string $where, array $params = []): void
    {
        self::query("DELETE FROM {$table} WHERE {$where}", $params);
    }
}
