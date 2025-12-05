<?php
declare(strict_types=1);
require __DIR__ . '/../auth_bootstrap.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'ユーザー名とパスワードを入力してください。';
    } else {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // ログイン成功
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['username'] = $user['username'];

            // ログイン後はトップへ
            header('Location: /index.php');
            exit;
        } else {
            $error = 'ユーザー名またはパスワードが違います。';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <title>ログイン</title>
</head>
<body style="font-family: sans-serif; padding: 20px;">

  <h1 style="font-size: 28px; text-align: center; margin-bottom: 20px;">
    ログインページ
  </h1>

  <?php if ($error): ?>
    <p style="color:red; font-size: 18px; text-align:center;">
      <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </p>
  <?php endif; ?>

  <form action="/login.php" method="POST"
        style="max-width: 380px; margin: 0 auto; border: 1px solid #ccc;
               padding: 20px; border-radius: 10px;">

    <label style="font-size: 18px;">ユーザー名:</label><br />
    <input type="text" name="username" required
           style="width: 100%; font-size: 18px; padding: 8px;
                  margin-top: 5px; margin-bottom: 15px;"><br />

    <label style="font-size: 18px;">パスワード:</label><br />
    <input type="password" name="password" required
           style="width: 100%; font-size: 18px; padding: 8px;
                  margin-top: 5px; margin-bottom: 20px;"><br />

    <button type="submit"
            style="width: 100%; padding: 12px; font-size: 18px;
                   border-radius: 8px; cursor: pointer;">
      ログイン
    </button>
  </form>

  <div style="text-align: center; margin-top: 20px; font-size: 16px;">
    <p><a href="/register.php">新規登録はこちら</a></p>
    <p><a href="/forgot.php">パスワードを忘れた場合はこちら</a></p>
  </div>

</body>
</html>
