<?php
require 'db.php';
session_start();

$requestId = $_POST['request_id'];
$response = $_POST['response']; // "accept" または "reject"

if (!isset($_SESSION['user_id'])) {
    echo "ログインしてください。";
    exit;
}

// 申請の状態を更新
$status = ($response === 'accept') ? 'accepted' : 'rejected';
$sql = "UPDATE chat_requests SET status = :status WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':status' => $status,
    ':id' => $requestId
]);

if ($response === 'accept') {
    // チャットルームを作成
    $request = $pdo->query("SELECT sender_id, receiver_id FROM chat_requests WHERE id = $requestId")->fetch(PDO::FETCH_ASSOC);
    $sql = "INSERT INTO chat_rooms (user1_id, user2_id, created_at) VALUES (:user1, :user2, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user1' => $request['sender_id'],
        ':user2' => $request['receiver_id']
    ]);

    echo "申請を承認し、チャットルームを作成しました！";
} else {
    echo "申請を拒否しました。";
}
header("Location: index.php");
