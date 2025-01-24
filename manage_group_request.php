<?php
require 'db.php'; // データベース接続
session_start(); // セッション開始

// ログイン確認
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// 承諾または拒否処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = $_POST['request_id'];
    $action = $_POST['action']; // 'accept' or 'reject'

    // グループリクエストを取得
    $sql = "SELECT * FROM group_requests WHERE id = :request_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':request_id' => $requestId]);
    $groupRequest = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($groupRequest) {
        if ($action === 'accept') {
            // リクエストを承諾した場合、group_members に登録
            $sql = "INSERT INTO group_members (group_id, user_id, role) VALUES (:group_id, :user_id, 'member')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':group_id' => $groupRequest['group_id'],
                ':user_id' => $groupRequest['user_id'],
            ]);

            // リクエストのステータスを更新
            $sql = "UPDATE group_requests SET status = 'accepted' WHERE id = :request_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':request_id' => $requestId]);

            echo "リクエストを承諾しました！";
        } elseif ($action === 'reject') {
            // リクエストのステータスを更新
            $sql = "UPDATE group_requests SET status = 'rejected' WHERE id = :request_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':request_id' => $requestId]);

            echo "リクエストを拒否しました。";
        }
    } else {
        echo "リクエストが見つかりません。";
    }

    header('Location: group_manage.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>グループリクエスト管理</title>
</head>
<body>
    <!-- 管理画面の内容 -->
</body>
</html>
