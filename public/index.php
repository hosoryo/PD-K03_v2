<?php
declare(strict_types=1);
require __DIR__ . '/../auth_bootstrap.php';
require_login(); 
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="survey.png">
    <link rel="stylesheet" href="style.css">
    <title>アンケート | ホーム</title>
</head>

<body>
    <header class="app-bar">
        <h1>アンケートサイト</h1> 
        
        <nav class="nav">
            <ul>
                <li><a href="index.php" class="tooltip" data-tip="ホーム画面に移る">ホーム</a></li>
                <li><a href="ank2.php" class="tooltip" data-tip="アンケートに回答できます">アンケートページ</a></li>
                <li><a href="mypage.php" class="tooltip" data-tip="アカウント情報や保有しているポイントを確認できます">マイページ</a></li>
                <li><a href="opinion.html" class="tooltip" data-tip="アプリに関する意見はこちらへ">意見箱</a></li>
                <li><a href="rire2.php" class="tooltip" data-tip="過去に回答したアンケートを確認できます">履歴</a></li>
                <li><a href="exchange.php" class="tooltip" data-tip="ポイントを利用して景品と交換することができます">景品交換はこちら</a></li>
                <li><a href="logout.php" class="tooltip" data-tip="ログアウトはこちら">ログアウト</a></li>
                <!-- 後ほどページにアクセスできるようにする -->
            </ul>
        </nav>
        <img src="click.png" alt="hukidasi" class="app-bar__badge">
    </header>


    <footer>
        <p>&copy; 2025 アンケートサイト</p>
    </footer>
</body>

</html>