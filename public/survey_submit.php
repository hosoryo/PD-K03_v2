<?php
declare(strict_types=1);
require __DIR__ . '/../auth_bootstrap.php';

// ★ ログインしているユーザーだけ
require_login();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// JSONで送る想定
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

$points     = isset($data['surveyPoints']) ? (int)$data['surveyPoints'] : 0;
$surveyId   = (string)($data['surveyId'] ?? '');
$surveyName = (string)($data['surveyName'] ?? '');

// answers は今回は保存しない（必要なら後で拡張できる）
$answers = $data['answers'] ?? null;

if ($points <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid points']);
    exit;
}

if ($surveyName === '') {
    // surveyNameが空だと履歴表示が微妙になるので最低限の補正
    $surveyName = $surveyId !== '' ? ('survey ' . $surveyId) : 'アンケート';
}

try {
    $pdo = get_pdo();

    // ★ 追加：履歴テーブルが無ければ作成（mypage.phpが参照するため）
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS survey_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            survey_name TEXT NOT NULL,
            points INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // ★ 追加：ポイント加算＋履歴保存はセットで行う（途中失敗を防ぐ）
    $pdo->beginTransaction();

    // ★ ポイントを加算（元の処理）
    $stmt = $pdo->prepare("
        UPDATE users
        SET points = COALESCE(points, 0) + :p
        WHERE id = :uid
    ");
    $stmt->execute([
        ':p'   => $points,
        ':uid' => $_SESSION['user_id'],
    ]);

    // ★ 追加：履歴として保存（←これがないとマイページの累計/最近の獲得が増えない）
    $stmtH = $pdo->prepare("
        INSERT INTO survey_history (user_id, survey_name, points)
        VALUES (:uid, :name, :p)
    ");
    $stmtH->execute([
        ':uid'  => $_SESSION['user_id'],
        ':name' => $surveyName,
        ':p'    => $points,
    ]);

    $pdo->commit();

    // 新しい合計ポイントを取得（マイページ側で使いたければ）
    $stmt2 = $pdo->prepare("SELECT COALESCE(points,0) AS points FROM users WHERE id = ?");
    $stmt2->execute([$_SESSION['user_id']]);
    $row = $stmt2->fetch(PDO::FETCH_ASSOC);
    $newPoints = (int)($row['points'] ?? 0);

    echo json_encode([
        'ok'        => true,
        'newPoints' => $newPoints,
    ]);
    exit;

} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'server error',
        'msg'   => $e->getMessage(),
    ]);
    exit;
}
