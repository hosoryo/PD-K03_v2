<?php
declare(strict_types=1);
require __DIR__ . '/../auth_bootstrap.php';
require_login();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>景品交換</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      background-color: #fafafa;
      text-align: center;
      padding: 40px;
    }
    h1 {
      color: #333;
      border-bottom: 2px solid #007bff;
      display: inline-block;
      padding-bottom: 10px;
    }
    .item {
      border: 1px solid #ccc;
      border-radius: 12px;
      background-color: #fff;
      width: 250px;
      margin: 20px auto;
      padding: 20px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .item button {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      cursor: pointer;
    }
    .item button:hover {
      background-color: #0056b3;
    }
    a {
      display: inline-block;
      margin-top: 30px;
      color: #007bff;
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
    .return-link { text-align: right; max-width: 900px; margin: 0 auto 20px; }
    .return-link a { padding: 12px 20px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 1.1em; display: inline-block; }
  </style>
</head>
<body>

<h1>🎁 景品交換ページ 🎁</h1>
<p>ポイントを使って、以下の景品と交換できます。</p>

<div class="item">
  <h3>商品券（100ポイント）</h3>
  <button onclick="exchange('スターバックスカード', 100)">交換する</button>
</div>

<div class="item">
  <h3>商品券（200ポイント）</h3>
  <button onclick="exchange('Amazonギフト券', 200)">交換する</button>
</div>

<div class="item">
  <h3>お菓子セット（50ポイント）</h3>
  <button onclick="exchange('お菓子セット', 50)">交換する</button>
</div>

<div class="return-link">
  <a href="index.html" aria-label="ホームに戻る">← ホームに戻る</a>
  </div>

<script>
async function exchange(itemName, cost) {
  const ok = confirm(`${itemName}（${cost}ポイント）と交換しますか？`);
  if (!ok) return;

  try {
    const resp = await fetch('/exchange_api.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({
        itemName: itemName,
        cost: String(cost),
      })
    });

    const ct = (resp.headers.get('content-type') || '').toLowerCase();

    if (!resp.ok) {
      const txt = await resp.text().catch(() => '');
      alert('サーバーエラーが発生しました。' + (txt ? `\n${txt}` : ''));
      return;
    }

    if (!ct.includes('application/json')) {
      const txt = await resp.text().catch(() => '');
      alert('ログインが切れた可能性があります。もう一度ログインしてください。' + (txt ? `\n${txt}` : ''));
      location.href = '/login.php';
      return;
    }

    const data = await resp.json();

    if (!data.ok) {
      alert((data.error || '交換に失敗しました') + (data.current != null ? `（現在 ${data.current} pt）` : ''));
      return;
    }

    alert(`✅ 交換しました！\n残りポイント：${data.newPoints} pt`);

  } catch (e) {
    console.error(e);
    alert('通信エラーが発生しました。');
  }
}
</script>

</body>
</html>
