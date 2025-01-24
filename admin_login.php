<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // 管理者認証（例: ユーザー名: admin, パスワード: admin123）
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['user_id'] = 'admin'; // 管理者用の特別なID
        $_SESSION['user_role'] = 'admin'; // 役割を「admin」と設定
        $_SESSION['user_name'] = '管理者'; // 表示用の名前
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $error = 'ログイン情報が正しくありません。';
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者ログイン</title>
    <style>
        .container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #ff7f50;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label, input, button {
            margin-bottom: 15px;
        }
        button {
            background-color: #ff7f50;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px;
            cursor: pointer;
        }
        button:hover {
            background-color: #ff6347;
        }
        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>管理者ログイン</h1>
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="username">ユーザー名:</label>
            <input type="text" name="username" id="username" required>
            
            <label for="password">パスワード:</label>
            <input type="password" name="password" id="password" required>
            
            <button type="submit">ログイン</button>
        </form>
    </div>
</body>
</html>
