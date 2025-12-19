<?php
declare(strict_types=1);
require __DIR__ . '/../auth_bootstrap.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
  exit;
}

$itemName = trim((string)($_POST['itemName'] ?? ''));
$cost     = (int)($_POST['cost'] ?? 0);

if ($itemName === '' || $cost <= 0) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'invalid params'], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  $pdo = get_pdo();
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $pdo->exec("
    CREATE TABLE IF NOT EXISTS prize_exchanges (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id INTEGER NOT NULL,
      item_name TEXT NOT NULL,
      cost_points INTEGER NOT NULL,
      created_at TEXT DEFAULT CURRENT_TIMESTAMP
    )
  ");

  $pdo->exec("
    CREATE TABLE IF NOT EXISTS survey_history (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id INTEGER NOT NULL,
      survey_name TEXT NOT NULL,
      points INTEGER NOT NULL,
      created_at TEXT DEFAULT CURRENT_TIMESTAMP
    )
  ");

  $pdo->beginTransaction();

  $userId = (int)$_SESSION['user_id'];

  $stmt = $pdo->prepare("SELECT COALESCE(points,0) AS points FROM users WHERE id = ?");
  $stmt->execute([$userId]);
  $current = (int)$stmt->fetchColumn();

  if ($current < $cost) {
    $pdo->rollBack();
    echo json_encode(['ok' => false, 'error' => 'ポイントが足りません', 'current' => $current], JSON_UNESCAPED_UNICODE);
    exit;
  }

  $stmt2 = $pdo->prepare("UPDATE users SET points = COALESCE(points,0) - :c WHERE id = :uid");
  $stmt2->execute([
    ':c'   => $cost,
    ':uid' => $userId,
  ]);

  $stmt3 = $pdo->prepare("
    INSERT INTO prize_exchanges (user_id, item_name, cost_points)
    VALUES (:uid, :item, :cost)
  ");
  $stmt3->execute([
    ':uid'  => $userId,
    ':item' => $itemName,
    ':cost' => $cost,
  ]);

  $stmt5 = $pdo->prepare("
    INSERT INTO survey_history (user_id, survey_name, points)
    VALUES (:uid, :name, :points)
  ");
  $stmt5->execute([
    ':uid'    => $userId,
    ':name'   => '景品交換：' . $itemName,
    ':points' => -$cost,
  ]);

  $stmt4 = $pdo->prepare("SELECT COALESCE(points,0) AS points FROM users WHERE id = ?");
  $stmt4->execute([$userId]);
  $newPoints = (int)$stmt4->fetchColumn();

  $pdo->commit();

  echo json_encode(['ok' => true, 'newPoints' => $newPoints], JSON_UNESCAPED_UNICODE);
  exit;

} catch (Throwable $e) {
  if (isset($pdo) && $pdo->inTransaction()) {
    $pdo->rollBack();
  }
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'server error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
  exit;
}
