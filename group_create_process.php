<?php
require 'db.php'; // データベース接続

// セッション開始（ログイン情報確認）
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// POST データを取得
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$public = $_POST['public'] ?? '0'; // デフォルトで非公開
$userId = $_SESSION['user_id'];

// バリデーション
if (empty($name) || empty($description)) {
    echo "グループ名と説明は必須です。";
    exit;
}

try {
    // トランザクション開始
    $pdo->beginTransaction();

    // グループを作成
    $sql = "INSERT INTO `user_groups` (name, description, public, created_by) VALUES (:name, :description, :public, :created_by)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => $name,
        ':description' => $description,
        ':public' => $public,
        ':created_by' => $userId
    ]);

    // 作成したグループの ID を取得
    $groupId = $pdo->lastInsertId();

    // 作成者をグループメンバー（管理者）として登録
    $memberSql = "INSERT INTO group_members (group_id, user_id, role) VALUES (:group_id, :user_id, 'admin')";
    $memberStmt = $pdo->prepare($memberSql);
    $memberStmt->execute([
        ':group_id' => $groupId,
        ':user_id' => $userId
    ]);

    // トランザクション完了
    $pdo->commit();

    // 作成後の詳細ページへリダイレクト
    header("Location: group_detail.php?group_id=$groupId");
    exit;

} catch (PDOException $e) {
    // エラー発生時はロールバック
    $pdo->rollBack();
    echo "エラーが発生しました: " . htmlspecialchars($e->getMessage());
    exit;
}
?>
