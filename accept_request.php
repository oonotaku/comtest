<?php
require 'db.php'; // データベース接続
session_start();

$requestId = $_GET['request_id'] ?? null;

if (!$requestId) {
    echo "無効なリクエストです。";
    exit;
}

// 申請を承認し、チャットルームを作成
try {
    $pdo->beginTransaction();

    $sql = "UPDATE chat_requests SET status = 'accepted' WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $requestId]);

    $chatRoomSql = "INSERT INTO chat_rooms (sender_id, receiver_id)
                    SELECT sender_id, receiver_id FROM chat_requests WHERE id = :id";
    $chatRoomStmt = $pdo->prepare($chatRoomSql);
    $chatRoomStmt->execute([':id' => $requestId]);

    $pdo->commit();
    echo "<script>alert('チャット申請を承認しました。'); window.location.href = 'index.php';</script>";
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "エラー: " . $e->getMessage();
    exit;
}
