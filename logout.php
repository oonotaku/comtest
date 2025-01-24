<?php
session_start();
require 'db.php'; // データベース接続ファイル

// 現在のユーザーIDを取得
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $logoutTimestamp = date('Y-m-d H:i:s');

    // ユーザーの最後のログアウト時刻を保存
    $sql = "UPDATE registrations SET last_logout = :logoutTimestamp WHERE id = :userId";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':logoutTimestamp' => $logoutTimestamp, ':userId' => $userId]);

    // フォロワー数とフォロー数を取得
    $followersQuery = "SELECT COUNT(*) FROM follows WHERE followed_id = :userId";
    $followersStmt = $pdo->prepare($followersQuery);
    $followersStmt->execute([':userId' => $userId]);
    $followersCount = $followersStmt->fetchColumn();

    $followingQuery = "SELECT COUNT(*) FROM follows WHERE follower_id = :userId";
    $followingStmt = $pdo->prepare($followingQuery);
    $followingStmt->execute([':userId' => $userId]);
    $followingCount = $followingStmt->fetchColumn();

    // 総チャット投稿数を取得
    $chatCountQuery = "SELECT COUNT(*) FROM chat_messages WHERE sender_id = :userId";
    $chatCountStmt = $pdo->prepare($chatCountQuery);
    $chatCountStmt->execute([':userId' => $userId]);
    $chatCount = $chatCountStmt->fetchColumn();

    // 総ツイート数といいね取得数を取得
    $tweetCountQuery = "SELECT COUNT(*) FROM tweets WHERE user_id = :userId";
    $tweetCountStmt = $pdo->prepare($tweetCountQuery);
    $tweetCountStmt->execute([':userId' => $userId]);
    $tweetCount = $tweetCountStmt->fetchColumn();

    $likeCountQuery = "SELECT COUNT(*) FROM tweet_likes WHERE tweet_id IN (SELECT id FROM tweets WHERE user_id = :userId)";
    $likeCountStmt = $pdo->prepare($likeCountQuery);
    $likeCountStmt->execute([':userId' => $userId]);
    $likeCount = $likeCountStmt->fetchColumn();

    // 必要な情報をセッションに保存（必要なら）
    $_SESSION['logout_stats'] = [
        'followers' => $followersCount,
        'following' => $followingCount,
        'chat_count' => $chatCount,
        'tweet_count' => $tweetCount,
        'like_count' => $likeCount,
        'logout_time' => $logoutTimestamp,
    ];
}

// リダイレクト先のページを決定
$redirectPage = isset($_SESSION['admin_logged_in']) ? 'admin_login.php' : 'login.php';

// セッションをクリアして破棄
session_unset();
session_destroy();

// リダイレクト
header("Location: $redirectPage");
exit;
