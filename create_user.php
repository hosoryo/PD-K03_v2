<?php
declare(strict_types=1);
require __DIR__ . '/auth_bootstrap.php';

$pdo = get_pdo();

// users テーブルを作る（無ければ作成）
$pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE,
        password_hash TEXT,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    )
");

// ★ 最初のユーザー（ここは好きなID/パスに変えてOK）
$username = 'admin';
$password = 'password123';

// すでに同じユーザー名があれば何もしない
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare(
    'INSERT OR IGNORE INTO users (username, password_hash) VALUES (?, ?)'
);
$stmt->execute([$username, $hash]);

echo "usersテーブルを用意しました。\n";
echo "ログイン用ユーザー:\n";
echo "  username: {$username}\n";
echo "  password: {$password}\n";
