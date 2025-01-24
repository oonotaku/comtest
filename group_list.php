<?php
require 'db.php'; // データベース接続
require 'header.php'; // 共通ヘッダー

// ログインしていない場合はリダイレクト
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // グループ情報取得
    $sql = "SELECT ug.id, ug.name, ug.description, ug.created_by, 
            EXISTS (
                SELECT 1 
                FROM group_members gm 
                WHERE gm.group_id = ug.id AND gm.user_id = :user_id
            ) AS is_member
            FROM user_groups ug";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "エラーが発生しました: " . htmlspecialchars($e->getMessage());
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>グループ一覧</title>
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #ff7f50;
        }
        .group-card {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 15px;
            background-color: #fff7e6;
        }
        .group-card h2 {
            color: #ff7f50;
            margin: 0;
        }
        .group-card p {
            margin: 10px 0;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #ff7f50;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #ff6347;
        }
        .chat-btn {
            background-color: #4CAF50;
        }
        .chat-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>グループ一覧</h1>

        <?php if (count($groups) > 0): ?>
            <?php foreach ($groups as $group): ?>
                <div class="group-card">
                    <h2><?php echo htmlspecialchars($group['name']); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($group['description'])); ?></p>

                    <!-- グループ詳細リンク -->
                    <a href="group_detail.php?group_id=<?php echo $group['id']; ?>" class="btn">詳細を見る</a>

                    <!-- グループチャットリンク（所属している場合のみ表示） -->
                    <?php if ($group['is_member']): ?>
                        <a href="group_chat_room.php?group_id=<?php echo $group['id']; ?>" class="btn chat-btn">グループチャットする</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center;">グループがありません。</p>
        <?php endif; ?>

        <p style="text-align: center;"><a href="group_create.php" class="btn">新しいグループを作成する</a></p>
    </div>
    <?php require 'footer.php'; // 共通フッター ?>
</body>
</html>
