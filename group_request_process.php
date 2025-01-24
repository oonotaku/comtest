<?php
require 'db.php'; // データベース接続

// セッション開始（ログイン情報確認）
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// POST データを取得
$groupId = $_POST['group_id'] ?? null;
$userId = $_SESSION['user_id'];

if (!$groupId) {
    echo "グループIDが指定されていません。";
    exit;
}

try {
    // グループ申請が既に存在しているか確認
    $checkSql = "SELECT * FROM group_requests WHERE group_id = :group_id AND user_id = :user_id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([
        ':group_id' => $groupId,
        ':user_id' => $userId,
    ]);
    $existingRequest = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingRequest) {
        echo "既に申請済みです。";
        exit;
    }

    // グループ申請を挿入
    $insertSql = "INSERT INTO group_requests (group_id, user_id, status) VALUES (:group_id, :user_id, 'pending')";
    $insertStmt = $pdo->prepare($insertSql);
    $insertStmt->execute([
        ':group_id' => $groupId,
        ':user_id' => $userId,
    ]);

    // 成功メッセージを表示
    echo "グループに申請しました！";
    header("Location: group_list.php"); // 必要に応じてリダイレクト
    exit;

} catch (PDOException $e) {
    echo "エラーが発生しました: " . htmlspecialchars($e->getMessage());
    exit;
}
