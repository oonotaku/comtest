<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // セッションが開始されていない場合にのみ開始
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // ログインページにリダイレクト
    exit;
}
?>
