<?php
declare(strict_types=1);
require __DIR__ . '/../auth_bootstrap.php';

// ★ ログイン必須
require_login();

$pdo = get_pdo();

// ログイン中ユーザーの情報取得
$stmt = $pdo->prepare("
    SELECT id,
           username,
           COALESCE(points, 0) AS points,
           created_at
    FROM users
    WHERE id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(500);
    echo 'ユーザー情報が取得できませんでした。';
    exit;
}

// 表示用
$displayName = $user['username'];
$username    = $user['username'];
$points      = (int)$user['points'];
$userId      = (int)$user['id'];
$createdAt   = $user['created_at'] ?? '';
$avatarInitial = function_exists('mb_substr')
    ? mb_substr($displayName, 0, 1, 'UTF-8')
    : substr($displayName, 0, 1);
?>
<!doctype html>
<html lang="ja">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>マイページ - アンケート＆ポイント</title>

<style>
  :root { --bg:#f6f7fb; --card:#ffffff; --accent:#2563eb; --muted:#6b7280; --success:#059669; }
  body { margin:0; font-family:system-ui, sans-serif; background:var(--bg); color:#111; }
  .container { max-width:1000px; margin:28px auto; padding:20px; }

  header { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:20px; }
  h1 { margin:0; font-size:20px; }
  nav a { color:var(--accent); text-decoration:none; margin-left:12px; font-size:14px; }

  .grid { display:grid; grid-template-columns: 1fr 320px; gap:20px; align-items:start; }
  .card { background:var(--card); padding:18px; border-radius:12px; box-shadow:0 6px 18px rgba(17,24,39,0.06); }

  .profile { display:flex; align-items:center; gap:14px; }
  .avatar { width:64px; height:64px; border-radius:50%; background:#e6eefc; display:flex; align-items:center; justify-content:center; font-weight:700; color:var(--accent); font-size:24px; }
  .userinfo { flex:1; }

  .points { text-align:right; }
  .points .num { font-size:24px; font-weight:700; color:var(--accent); }

  h2 { margin:0 0 12px 0; font-size:16px; }

  .survey-list, .notif-list { display:flex; flex-direction:column; gap:10px; }
  .survey-item { display:flex; justify-content:space-between; gap:12px; padding:12px; border-radius:8px; background:#fbfdff; border:1px solid #eef2ff; }
  .muted { color:var(--muted); font-size:13px; }

  .btn { display:inline-block; padding:8px 12px; border-radius:8px; background:var(--accent); color:#fff; text-decoration:none; font-size:14px; border:none; cursor:pointer; }
  .btn.secondary { background:#fff; color:var(--accent); border:1px solid #dbeafe; }

  /* ★ ログアウトボタン（赤） */
  .btn.logout-btn {
    background:#e11d48;
    color:#fff;
    border:none;
  }
  .btn.logout-btn:hover { opacity:.9; }
  .btn.logout-btn:active { transform:translateY(1px); }

  footer { text-align:center; margin-top:22px; color:var(--muted); font-size:13px; }

  @media (max-width:900px) {
    .grid { grid-template-columns:1fr; }
    .points { text-align:left; margin-top:8px; }
  }

  /* simple modal */
  .modal { position:fixed; inset:0; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.45); padding:20px; visibility:hidden; opacity:0; transition:.18s; }
  .modal.show { visibility:visible; opacity:1; }
  .modal .box { width:100%; max-width:520px; background:#fff; border-radius:10px; padding:18px; }
</style>
</head>

<body>
  <div class="container">

    <header>
      <div>
        <h1>アンケート＆ポイント - マイページ</h1>
        <div class="muted">ユーザー専用ページ｜ポイント・回答履歴</div>
      </div>
      <nav>
        <a href="/index.php">アンケート</a>
        <a href="/opinion.html">意見箱</a>
        <a href="/logout.php">ログアウト</a>
      </nav>
    </header>

    <main class="grid">

      <!-- ▼ LEFT main card -->
      <section class="card">

        <div class="profile">
          <div class="avatar">
            <?php echo htmlspecialchars($avatarInitial, ENT_QUOTES, 'UTF-8'); ?>
          </div>
          <div class="userinfo">
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <div>
                <div style="font-weight:700; font-size:16px">
                  <?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div class="muted">@<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php if ($createdAt): ?>
                  <div class="muted" style="font-size:12px;">登録日: <?php echo htmlspecialchars($createdAt, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
              </div>

              <div class="points">
                <div class="muted">保有ポイント</div>
                <div class="num"><?php echo $points; ?> pt</div>
              </div>
            </div>
          </div>
        </div>

        <!-- ★ ここにログアウトボタンを追加 -->
        <div style="text-align:right; margin-top:12px;">
          <a href="/logout.php" class="btn logout-btn">ログアウト</a>
        </div>

        <hr style="margin:14px 0; border:none; border-top:1px solid #eef2ff;">

        <h2>アンケート履歴</h2>
        <div class="muted" style="margin-bottom:8px;">※ 今はダミー表示</div>

        <div class="survey-list">
          <div class="survey-item">
            <div>
              <div style="font-weight:600">サンプルアンケート</div>
              <div class="muted">回答日: 2025-11-01</div>
            </div>
            <div style="text-align:right">
              <div class="muted">獲得</div>
              <div style="font-weight:700;color:var(--success)">+10 pt</div>
            </div>
          </div>
        </div>

        <div style="margin-top:18px; display:flex; gap:10px;">
          <button class="btn" onclick="location.href='/index.php'">新しいアンケート</button>
          <button class="btn secondary" id="btnExchange">ポイント交換</button>
        </div>

      </section>

      <!-- ▼ RIGHT sidebar -->
      <aside class="card">
        <h2>アカウント情報</h2>

        <div class="muted">アカウントID</div>
        <div style="font-weight:700; margin-bottom:12px">#<?php echo $userId; ?></div>

        <div class="muted">最近の獲得（例）</div>
        <ul style="padding-left:0; list-style:none; margin-top:8px;">
          <li style="background:#f1f5f9; padding:6px 8px; border-radius:8px;">+10pt · サンプル</li>
        </ul>

        <div class="muted" style="margin-top:16px;">クイックリンク</div>
        <div style="display:flex; flex-direction:column; gap:8px; margin-top:8px;">
          <a class="btn secondary" href="/index.php">アンケート一覧</a>
          <a class="btn secondary" href="/keihin.html">景品交換</a>
        </div>

      </aside>
    </main>

    <footer>
      <div class="muted">© 2025 アンケートサイト</div>
    </footer>
  </div>

  <!-- modal -->
  <div id="modal" class="modal">
    <div class="box">
      <h3>ポイント交換</h3>
      <p class="muted">※ この機能は後で実装予定です。</p>
      <button class="btn secondary" onclick="document.getElementById('modal').classList.remove('show')">閉じる</button>
    </div>
  </div>

<script>
document.getElementById('btnExchange').addEventListener('click', () => {
  document.getElementById('modal').classList.add('show');
});
</script>

</body>
</html>
