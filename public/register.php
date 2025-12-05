<?php
declare(strict_types=1);
require __DIR__ . '/../auth_bootstrap.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'ユーザー名とパスワードを入力してください。';
    } else {

      // パスワードのセキュリティチェック
      if (strlen($password) < 6) {
        $error = 'パスワードは6文字以上で入力してください。';
      } elseif (!preg_match('/[A-Za-z]/', $password)) {
        $error = 'パスワードに英字を含めてください。';
      } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'パスワードに数字を含めてください。';
      } 

        $pdo = get_pdo();

        // 同名ユーザーが存在するか確認
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $exists = $stmt->fetchColumn();

        if ($exists) {
            $error = 'このユーザー名は既に使用されています。';
        } else {
            // 登録処理
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
            $stmt->execute([$username, $hash]);

            $success = '登録が完了しました。ログインしてください。';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <title>新規登録</title>
</head>
<body style="font-family: sans-serif; padding: 20px;">

  <h1 style="font-size: 28px; text-align: center; margin-bottom: 20px;">新規登録</h1>

  <?php if ($error): ?>
    <p style="color:red; font-size: 18px; text-align:center;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
  <?php endif; ?>

  <?php if ($success): ?>
    <p style="color:green; font-size: 18px; text-align:center;"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
  <?php endif; ?>

  <form action="/register.php" method="POST" 
        style="max-width: 380px; margin: 0 auto; border: 1px solid #ccc; padding: 20px; border-radius: 10px;">

    <label style="font-size: 18px;">ユーザー名:</label><br>
    <input type="text" name="username" required
           style="width: 100%; font-size: 18px; padding: 8px; margin-top: 5px; margin-bottom: 15px;"><br>

    <label style="font-size: 18px;">パスワード:</label><br>
    <span style="font-size: 12px; color: #555;">（6文字以上・英字と数字を含めてください）</span><br>
    <input type="password" name="password" required
           style="width: 100%; font-size: 18px; padding: 8px; margin-top: 5px; margin-bottom: 20px;"><br>

    <button type="submit"
            style="width: 100%; padding: 12px; font-size: 18px; border-radius: 8px; cursor: pointer;">
        登録
    </button>
  </form>

  <p style="text-align: center; margin-top: 20px; font-size: 16px;">
    <a href="/login.php">ログイン画面へ戻る</a>
  </p>

</body>
</html>
