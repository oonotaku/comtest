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
    $pdo->beginTransaction();

    // Likeの状態を確認
    $checkSql = "SELECT COUNT(*) FROM tweet_likes WHERE tweet_id = :tweet_id AND user_id = :user_id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':tweet_id' => $tweetId, ':user_id' => $userId]);
    $isLiked = $checkStmt->fetchColumn() > 0;

    if ($isLiked) {
        // 既にLike済みの場合、削除
        $deleteSql = "DELETE FROM tweet_likes WHERE tweet_id = :tweet_id AND user_id = :user_id";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->execute([':tweet_id' => $tweetId, ':user_id' => $userId]);

        $status = 'unliked';
    } else {
        // Likeが存在しない場合、追加
        $insertSql = "INSERT INTO tweet_likes (tweet_id, user_id) VALUES (:tweet_id, :user_id)";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([':tweet_id' => $tweetId, ':user_id' => $userId]);

        $status = 'liked';
    }

    // Likeの数を取得
    $countSql = "SELECT COUNT(*) FROM tweet_likes WHERE tweet_id = :tweet_id";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([':tweet_id' => $tweetId]);
    $likeCount = $countStmt->fetchColumn();

    $pdo->commit();

    echo json_encode(['status' => $status, 'like_count' => $likeCount]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Failed to toggle like']);
}
?>
