<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/auth_bootstrap.php';

$pdo = get_pdo();

// users テーブル（ログイン用）
$pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE,
        password_hash TEXT,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    );
");

// opinions テーブル（意見用）
$pdo->exec("
    CREATE TABLE IF NOT EXISTS opinions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        email TEXT,
        message TEXT,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    );
");

echo "OK: tables created in " . realpath(__DIR__ . '/opinions.db') . "\n";