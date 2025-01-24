<?php
require 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POSTデータを取得
    $parentTweetId = intval($_POST['parent_tweet_id']);
    $content = trim($_POST['content']);

    if (empty($content)) {
        echo "リプライ内容を入力してください。";
        exit;
    }

    try {
        // データベースにリプライを挿入
        $sql = "INSERT INTO tweet_replies (parent_tweet_id, user_id, content) VALUES (:parent_tweet_id, :user_id, :content)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':parent_tweet_id' => $parentTweetId,
            ':user_id' => $userId,
            ':content' => $content
        ]);

        // 元のページにリダイレクト
        header("Location: tweet_detail.php?tweet_id=$parentTweetId");
        exit;
    } catch (PDOException $e) {
        echo "エラーが発生しました: " . htmlspecialchars($e->getMessage());
    }
} else {
    echo "無効なリクエストです。";
}
?>
