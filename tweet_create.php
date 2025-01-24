<?php
require 'db.php';
session_start();

// ログイン確認
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// ユーザーIDを取得
$userId = $_SESSION['user_id'];

// フォームからのデータを取得
$content = trim($_POST['content'] ?? '');
$hashtags = '';
$originalTweetId = null;
$imagePath = '';

// コンテンツが空の場合はエラー
if (empty($content) && empty($_FILES['image']['tmp_name'])) {
    header('Location: tweet.php?error=empty');
    exit;
}

// 画像がアップロードされている場合
if (!empty($_FILES['image']['tmp_name'])) {
    $imageDir = 'uploads/';
    $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
    $imagePath = $imageDir . $imageName;
    move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
}

// ハッシュタグを抽出
if (!empty($content)) {
    preg_match_all('/#\w+/', $content, $matches);
    $hashtags = implode(',', $matches[0]);
}

// データベースにツイートを保存
try {
    $sql = "INSERT INTO tweets (user_id, content, hashtags, original_tweet_id, image_path) 
            VALUES (:user_id, :content, :hashtags, :original_tweet_id, :image_path)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $userId,
        ':content' => $content,
        ':hashtags' => $hashtags,
        ':original_tweet_id' => $originalTweetId,
        ':image_path' => $imagePath,
    ]);
    header('Location: tweet.php?success=1');
    exit;
} catch (Exception $e) {
    header('Location: tweet.php?error=failed');
    exit;
}
?>
