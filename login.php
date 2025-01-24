<?php
require 'header.php'; // 共通ヘッダー
require 'db.php';
// session_start();

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'];

    $sql = "SELECT * FROM registrations WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];  // ユーザー名をセッションに保存
        header('Location: index.php');
        exit;
    } else {
        $errorMessage = "ログイン情報が正しくありません。";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログインページ</title>
    <style>
body {
    margin: 0;
    font-family: 'Arial', sans-serif;
    background-color: #fff7e6;
}

.container {
    max-width: 400px;
    margin: 100px auto;
    padding: 20px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
}

h1 {
    margin-bottom: 20px;
    color: #ff7f50;
}

.input-group {
    margin-bottom: 15px;
    text-align: left;
}

label {
    font-weight: bold;
    color: #333;
}

input {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1rem;
}

button {
    width: 100%;
    padding: 10px;
    background-color: #ff7f50;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    cursor: pointer;
}

button:hover {
    background-color: #ff6347;
}

.error-message {
    color: red;
    font-weight: bold;
    margin-top: 15px;
}
    </style>
</head>
<body>
    <div class="container">
        <h1>ログイン</h1>
        <?php if (!empty($errorMessage)): ?>
            <p class="error-message"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>
        <form method="post" action="login.php">
            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="input-group">
                <label for="password">パスワード:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">ログイン</button>
        </form>
        <p style="margin-top: 20px;">アカウントをお持ちでない方は<a href="touroku.php" style="color: #ff6347; text-decoration: none;">新規登録</a></p>
    </div>
    <?php require 'footer.php'; // 共通フッター ?>
</body>
</html>
