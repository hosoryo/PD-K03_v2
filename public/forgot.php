<?php
declare(strict_types=1);
require __DIR__ . '/../auth_bootstrap.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $newPassword = trim($_POST['newpassword'] ?? '');

    if ($username === '' || $newPassword === '') {
        $error = 'すべての項目を入力してください。';
    } else {
        $pdo = get_pdo();

        // ユーザー存在チェック
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $userId = $stmt->fetchColumn();

        if (!$userId) {
            $error = 'このユーザーは存在しません。';
        } else {
            // パスワード再設定
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$hash, $userId]);

            $success = 'パスワードを変更しました。ログインしてください。';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <title>パスワード再設定</title>
</head>
<body style="font-family: sans-serif; padding: 20px;">

  <h1 style="font-size: 28px; text-align: center; margin-bottom: 20px;">
    パスワード再設定
  </h1>

  <?php if ($error): ?>
    <p style="color:red; font-size: 18px; text-align:center;">
      <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </p>
  <?php endif; ?>

  <?php if ($success): ?>
    <p style="color:green; font-size: 18px; text-align:center;">
      <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    </p>
  <?php endif; ?>

  <form action="/forgot" method="POST"
        style="max-width: 380px; margin: 0 auto; border: 1px solid #ccc;
               padding: 20px; border-radius: 10px;">

    <label style="font-size: 18px;">ユーザー名:</label><br>
    <input type="text" name="username" required
           style="width: 100%; font-size: 18px; padding: 8px;
                  margin-top: 5px; margin-bottom: 15px;"><br>

    <label style="font-size: 18px;">新しいパスワード:</label><br>
    <input type="password" name="newpassword" required
           style="width: 100%; font-size: 18px; padding: 8px;
                  margin-top: 5px; margin-bottom: 20px;"><br>

    <button type="submit"
            style="width: 100%; padding: 12px; font-size: 18px;
                   border-radius: 8px; cursor: pointer;">
      再設定
    </button>
  </form>

  <p style="text-align: center; margin-top: 20px; font-size: 16px;">
    <a href="/">ログイン画面へ戻る</a>
  </p>

</body>
</html>
