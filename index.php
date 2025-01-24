<?php
require 'db.php'; // データベース接続ファイルを読み込み
require 'header.php';
require 'tweet_form.php';

// セッションの開始
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ログイン中のユーザーIDを取得
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];

// ログアウト日時を取得
$sqlLogout = "SELECT last_logout FROM registrations WHERE id = :userId";
$stmtLogout = $pdo->prepare($sqlLogout);
$stmtLogout->execute([':userId' => $userId]);
$lastLogout = $stmtLogout->fetchColumn() ?: 'データなし';

// ダッシュボード情報を取得
$sqlData = "
    SELECT 
        (SELECT COUNT(*) FROM follows WHERE followed_id = :userId) AS followers_count,
        (SELECT COUNT(*) FROM follows WHERE follower_id = :userId) AS following_count,
        (SELECT COUNT(*) FROM chat_messages WHERE sender_id = :userId) AS total_chat_count,
        (SELECT COUNT(*) FROM tweets WHERE user_id = :userId) AS total_tweet_count,
        (SELECT COUNT(*) 
         FROM tweet_likes 
         JOIN tweets ON tweet_likes.tweet_id = tweets.id
         WHERE tweets.user_id = :userId) AS total_like_count
";
$stmtData = $pdo->prepare($sqlData);
$stmtData->execute([':userId' => $userId]);
$data = $stmtData->fetch(PDO::FETCH_ASSOC);

// 値を取得（nullの場合は0に変換）
$followersCount = $data['followers_count'] ?? 0;
$followingCount = $data['following_count'] ?? 0;
$totalChatCount = $data['total_chat_count'] ?? 0;
$totalTweetCount = $data['total_tweet_count'] ?? 0;
$totalLikeCount = $data['total_like_count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ダッシュボード</title>
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #ff7f50;
        }
        .group-card {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 15px;
            background-color: #fff7e6;
        }
        .group-card h2 {
            color: #ff7f50;
            margin: 0;
        }
        .group-card p {
            margin: 10px 0;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #ff7f50;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #ff6347;
        }
        .chat-btn {
            background-color: #4CAF50;
        }
        .chat-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>ようこそ、<?php echo htmlspecialchars($_SESSION['user_name']); ?>さん！</h1>
    <p>最終ログアウト日時: <?php echo htmlspecialchars($lastLogout); ?>時点で</p>
    <ul>
        <li>あなたをフォローしている人数: <?php echo $followersCount; ?>人</li>
        <li>あなたがフォローしている人数: <?php echo $followingCount; ?>人</li>
        <li>今までの総チャット投稿数: <?php echo $totalChatCount; ?>通</li>
        <li>今までの総つぶやき数: <?php echo $totalTweetCount; ?>回</li>
        <li>内いいね取得数: <?php echo $totalLikeCount; ?>回</li>
    </ul>
</body>
</html>
