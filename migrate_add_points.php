<?php
declare(strict_types=1);

require __DIR__ . '/auth_bootstrap.php';

$pdo = get_pdo();

try {
    // users テーブルに points カラムを追加（まだ無い場合）
    $pdo->exec("
        ALTER TABLE users
        ADD COLUMN points INTEGER NOT NULL DEFAULT 0
    ");
    echo "OK: users.points カラムを追加しました\n";
} catch (Throwable $e) {
    // すでにカラムがある場合などはここに来るので、今回は無視してOK
    echo "info: " . $e->getMessage() . "\n";
}
