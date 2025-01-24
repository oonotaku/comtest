<?php
require 'db.php'; // DB接続
session_start(); // セッション開始

// ユーザーがログインしているか確認
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'ログインしてください。']);
    exit;
}

// グループIDの取得
$groupId = $_GET['group_id'] ?? null;
if (!$groupId) {
    echo json_encode(['error' => 'グループIDが指定されていません。']);
    exit;
}

// メッセージを取得
$sql = "SELECT gm.message, gm.created_at, gm.sender_id, r.name AS sender_name 
        FROM group_messages gm
        JOIN registrations r ON gm.sender_id = r.id
        WHERE gm.group_id = :group_id
        ORDER BY gm.created_at ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':group_id' => $groupId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// メッセージをJSON形式で返す
header('Content-Type: application/json');
echo json_encode($messages);
