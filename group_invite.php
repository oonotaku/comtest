<?php
require 'db.php'; // データベース接続
require 'header.php'; // 共通ヘッダー

// セッション開始（ログイン確認）
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// POST データを取得
$groupId = $_POST['group_id'] ?? null;
$userIds = $_POST['user_ids'] ?? [];

if (!$groupId) {
    echo "グループが指定されていません。";
    exit;
}

if (empty($userIds)) {
    echo "招待するユーザーが選択されていません。";
    exit;
}

try {
    // トランザクションを開始
    $pdo->beginTransaction();

    foreach ($userIds as $userId) {
        // 招待情報を group_requests テーブルに追加
        $sql = "INSERT INTO group_requests (group_id, user_id, status) 
                VALUES (:group_id, :user_id, 'invited')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':group_id' => $groupId,
            ':user_id' => $userId
        ]);
    }

    // トランザクションをコミット
    $pdo->commit();

    echo "招待を送信しました。<br>";
    echo "<a href='group_detail.php?group_id=$groupId'>グループ詳細に戻る</a>";

} catch (PDOException $e) {
    // エラー時にロールバック
    $pdo->rollBack();
    echo "エラーが発生しました: " . htmlspecialchars($e->getMessage());
    exit;
}
?>
