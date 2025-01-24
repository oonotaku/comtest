<?php
// セッション開始
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ユーザー情報取得
$userName = $_SESSION['user_name'] ?? null;
?>
<header class="navbar">
    <a href="index.php" class="logo">INDEX</a>
    <div class="nav-right">
        <?php if ($userName): ?>
            <a href="users.php" class="nav-link">users</a>
            <a href="tweet.php" class="nav-link">tweet</a>
            <a href="group_list.php" class="nav-link">group</a>
            <a href="requests.php" class="nav-link">requests</a>
<a href="detail.php?id=<?php echo urlencode($_SESSION['user_id']); ?>" class="username-link">
    welcome <?php echo htmlspecialchars($userName); ?>
</a>
            <a href="logout.php" class="logout-link">logout</a>
        <?php else: ?>
            <a href="login.php" class="login-link">ログイン</a>
            <a href="touroku.php" class="register-link" style="margin-left: 10px;">新規登録</a>
        <?php endif; ?>
    </div>
</header>

<style>
body {
    margin: 0;
    font-family: 'Arial', sans-serif;
    background-color: #fff7e6;
}

.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background-color: #ff7f50;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    color: white;
}

.navbar a {
    text-decoration: none;
    color: white;
    font-weight: bold;
    margin-left: 15px;
}

.navbar a:hover {
    text-decoration: underline;
}

.logo {
    font-size: 1.5rem;
    font-weight: bold;
    color: white;
}

.username-link {
    margin-left: 10px;
    font-weight: normal;
    color: white;
}
@media (max-width: 600px) {
    .navbar {
        flex-direction: column;
        align-items: flex-start;
    }

    .navbar a {
        margin: 5px 0;
    }

    .container {
        width: 90%;
        padding: 15px;
    }
</style>
