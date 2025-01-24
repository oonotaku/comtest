<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$groupId = $_POST['group_id'] ?? null;
$userIds = $_POST['user_ids'] ?? [];

if (!$groupId || empty($userIds)) {
    echo "招待するユーザーを選択してください。";
    exit;
}

try {
    $pdo->beginTransaction();

    foreach ($userIds as $userId) {
        $sql = "INSERT INTO group_requests (group_id, user_id, status) VALUES (:group_id, :user_id, 'invited')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':group_id' => $groupId,
            ':user_id' => $userId
        ]);
    }

    $pdo->commit();
    echo "招待を送信しました。";
    header("Location: group_detail.php?group_id=$groupId");
    exit;
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "エラーが発生しました: " . htmlspecialchars($e->getMessage());
    exit;
}
?>
