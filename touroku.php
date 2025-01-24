<?php 
    require 'header.php'; // 共通ヘッダー  
    
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登録画面</title>
    <style>
body {
    margin: 0;
    font-family: 'Arial', sans-serif;
    background-color: #fff7e6;
}

.container {
    max-width: 600px;
    margin: 60px auto;
    padding: 30px;
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

h1 {
    text-align: center;
    color: #ff7f50;
    margin-bottom: 30px;
}

.input-group {
    margin-bottom: 20px;
}

label {
    font-weight: bold;
    margin-bottom: 8px;
    display: block;
}

input, textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1rem;
    box-sizing: border-box;
}

input[type="file"] {
    padding: 0;
}

button {
    width: 100%;
    padding: 15px;
    background-color: #ff7f50;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 1.2rem;
    cursor: pointer;
}

button:hover {
    background-color: #ff6347;
}

.upload-label {
    display: inline-block;
    padding: 10px 20px;
    background-color: #ff7f50;
    color: white;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
}

.upload-label:hover {
    background-color: #ff6347;
}

input[type="file"] {
    display: none; /* デフォルトのファイル選択ボタンを隠す */
}

p {
    text-align: center;
    margin-top: 20px;
}

p a {
    color: #ff6347;
    text-decoration: none;
}

p a:hover {
    text-decoration: underline;
}
    </style>
</head>
<body>
    <div class="container">
        <h1>登録ページ</h1>
        <form action="write.php" method="post" enctype="multipart/form-data">
            <div class="input-group">
                <label for="name">名前:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="input-group">
                <label for="password">パスワード:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="input-group">
                <label for="company">所属会社:</label>
                <input type="text" id="company" name="company">
            </div>
            <div class="input-group">
                <label for="position">役職:</label>
                <input type="text" id="position" name="position">
            </div>
            <div class="input-group">
                <label for="memo">備考:</label>
                <textarea id="memo" name="memo" rows="4"></textarea>
            </div>
            <div class="input-group">
                <label class="upload-label" for="photo">写真を選択</label>
                <input type="file" id="photo" name="photo" accept="image/*">
            </div>
            <button type="submit">登録</button>
        </form>
        <p>すでにアカウントをお持ちの方は <a href="login.php">ログイン</a></p>
    </div>
        <?php require 'footer.php'; // 共通フッター ?>
</body>
</html>
