<?php
require 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['tweet_id'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$tweetId = $data['tweet_id'];

try {
    // トランザクションを開始
    $pdo->beginTransaction();

    // 元のツイートを取得
    $sql = "SELECT content, image_path, hashtags FROM tweets WHERE id = :tweet_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':tweet_id' => $tweetId]);
    $originalTweet = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$originalTweet) {
        echo json_encode(['error' => 'Original tweet not found']);
        exit;
    }

    // ユーザーが既にこのツイートをリポストしているか確認
    $checkSql = "SELECT id FROM tweet_reposts WHERE original_tweet_id = :tweet_id AND user_id = :user_id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':tweet_id' => $tweetId, ':user_id' => $userId]);
    $existingRepost = $checkStmt->fetch();

    if ($existingRepost) {
        // 既存のリポストを削除
        $deleteSql = "DELETE FROM tweet_reposts WHERE id = :id";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->execute([':id' => $existingRepost['id']]);

        // トランザクションを確定
        $pdo->commit();

        // カウントを取得して返す
        $countSql = "SELECT COUNT(*) AS repost_count FROM tweet_reposts WHERE original_tweet_id = :tweet_id";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute([':tweet_id' => $tweetId]);
        $countResult = $countStmt->fetch();

        echo json_encode(['status' => 'unreposted', 'repost_count' => $countResult['repost_count']]);
        exit;
    } else {
        // 新しいリポストを挿入
        $insertRepostSql = "INSERT INTO tweet_reposts (original_tweet_id, user_id) VALUES (:tweet_id, :user_id)";
        $insertRepostStmt = $pdo->prepare($insertRepostSql);
        $insertRepostStmt->execute([':tweet_id' => $tweetId, ':user_id' => $userId]);

        // `tweets` テーブルにリポストを新規投稿として挿入
        $insertTweetSql = "INSERT INTO tweets (user_id, content, image_path, hashtags, original_tweet_id) 
                           VALUES (:user_id, :content, :image_path, :hashtags, :original_tweet_id)";
        $insertTweetStmt = $pdo->prepare($insertTweetSql);
        $insertTweetStmt->execute([
            ':user_id' => $userId,
            ':content' => $originalTweet['content'],
            ':image_path' => $originalTweet['image_path'],
            ':hashtags' => $originalTweet['hashtags'],
            ':original_tweet_id' => $tweetId
        ]);

        // トランザクションを確定
        $pdo->commit();

        // カウントを取得して返す
        $countSql = "SELECT COUNT(*) AS repost_count FROM tweet_reposts WHERE original_tweet_id = :tweet_id";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute([':tweet_id' => $tweetId]);
        $countResult = $countStmt->fetch();

        echo json_encode(['status' => 'reposted', 'repost_count' => $countResult['repost_count']]);
        exit;
    }
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Failed to process repost', 'details' => $e->getMessage()]);
    exit;
}
?>

