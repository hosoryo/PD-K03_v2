<?php
// C:\PD\opinion-box\auth_bootstrap.php
declare(strict_types=1);

session_start();

// エラー表示（開発中のみ）
ini_set('display_errors','1');
error_reporting(E_ALL);

// タイムゾーン
date_default_timezone_set('Asia/Tokyo');

// DB接続（プロジェクト直下の opinions.db を使用）
function get_pdo(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dbPath = __DIR__ . '/opinions.db';
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $pdo;
}

// ログイン済みか判定
function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
}
