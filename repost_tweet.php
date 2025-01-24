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
    // 元のツイートを取得
    $sql = "SELECT content, image_path, hashtags FROM tweets WHERE id = :tweet_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':tweet_id' => $tweetId]);
    $originalTweet = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$originalTweet) {
        echo json_encode(['error' => 'Original tweet not found']);
        exit;
    }

    // リポストとして新しいツイートを作成
    $insertSql = "INSERT INTO tweets (user_id, content, image_path, hashtags, original_tweet_id) 
                  VALUES (:user_id, :content, :image_path, :hashtags, :original_tweet_id)";
    $insertStmt = $pdo->prepare($insertSql);
    $insertStmt->execute([
        ':user_id' => $userId,
        ':content' => $originalTweet['content'],
        ':image_path' => $originalTweet['image_path'],
        ':hashtags' => $originalTweet['hashtags'],
        ':original_tweet_id' => $tweetId,
    ]);

    // リポスト後の新しいツイート ID を取得
    $newTweetId = $pdo->lastInsertId();

    echo json_encode(['status' => 'success', 'new_tweet_id' => $newTweetId]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to repost tweet', 'message' => $e->getMessage()]);
}
?>
