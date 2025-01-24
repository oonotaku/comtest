<?php
require 'db.php';
session_start();

// ユーザーがログインしているか確認
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$currentUserId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

// フォロー対象のユーザーIDを取得
if (!isset($data['user_id'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$targetUserId = $data['user_id'];

try {
    // フォロー状態を確認
    $checkFollow = $pdo->prepare("SELECT 1 FROM follows WHERE follower_id = :currentUserId AND followed_id = :targetUserId");
    $checkFollow->execute([
        ':currentUserId' => $currentUserId,
        ':targetUserId' => $targetUserId
    ]);

    if ($checkFollow->fetchColumn()) {
        // フォロー済みの場合は削除
        $deleteFollow = $pdo->prepare("DELETE FROM follows WHERE follower_id = :currentUserId AND followed_id = :targetUserId");
        $deleteFollow->execute([
            ':currentUserId' => $currentUserId,
            ':targetUserId' => $targetUserId
        ]);
        echo json_encode(['status' => 'unfollowed']);
    } else {
        // フォローしていない場合は追加
        $insertFollow = $pdo->prepare("INSERT INTO follows (follower_id, followed_id) VALUES (:currentUserId, :targetUserId)");
        $insertFollow->execute([
            ':currentUserId' => $currentUserId,
            ':targetUserId' => $targetUserId
        ]);
        echo json_encode(['status' => 'followed']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to toggle follow']);
}
