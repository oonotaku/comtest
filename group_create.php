<?php
require 'db.php'; // データベース接続
require 'header.php'; // 共通ヘッダー

// ログインしていない場合はリダイレクト
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>グループ作成</title>
    <style>
        .container {
            max-width: 600px;
            margin: 50px auto;
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
        label {
            margin-top: 10px;
            font-weight: bold;
        }
        input, textarea, select, button {
            margin-top: 5px;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            margin-top: 20px;
            background-color: #ff7f50;
            color: #fff;
            cursor: pointer;
            border: none;
        }
        button:hover {
            background-color: #ff6347;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>グループ作成</h1>
        <form method="POST" action="group_create_process.php">
            <label for="name">グループ名:</label>
            <input type="text" name="name" id="name" required>

            <label for="description">説明:</label>
            <textarea name="description" id="description" rows="5" required></textarea>

            <label for="public">公開設定:</label>
            <select name="public" id="public">
                <option value="1">公開</option>
                <option value="0">非公開</option>
            </select>

            <button type="submit">作成する</button>
        </form>
    </div>
</body>
</html>
