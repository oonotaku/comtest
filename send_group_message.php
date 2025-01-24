<?php
require 'db.php'; // DB接続
session_start(); // セッション開始

// ユーザーがログインしているか確認
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'ログインしてください。']);
    exit;
}

// 入力値の取得
$groupId = $_POST['group_id'] ?? null;
$message = $_POST['message'] ?? null;

if (!$groupId || !$message) {
    echo json_encode(['error' => 'すべての項目を入力してください。']);
    exit;
}

// メッセージを保存
$sql = "INSERT INTO group_messages (group_id, sender_id, message, created_at) 
        VALUES (:group_id, :sender_id, :message, NOW())";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':group_id' => $groupId,
    ':sender_id' => $_SESSION['user_id'],
    ':message' => $message
]);

echo json_encode(['success' => true]);
