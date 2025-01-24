<?php
// session_start();
require 'db.php';
require 'header.php';

// ログインしていない場合はリダイレクト
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// 自分が作成したグループの参加申請を取得
$sql = "SELECT gr.id AS request_id, g.name AS group_name, r.name AS user_name, gr.status, g.id AS group_id
        FROM group_requests gr
        JOIN groups g ON gr.group_id = g.id
        JOIN registrations r ON gr.user_id = r.id
        WHERE g.created_by = :user_id AND gr.status = 'pending'";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $userId]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>参加申請一覧</title>
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
        }
        .request-card {
            background-color: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }
        button {
            background-color: #ff7f50;
            color: white;
            border: none;
            padding: 8px 12px;
            margin-right: 5px;
            border-radius: 4px;
            cursor: pointer;
        }
        button.reject {
            background-color: #e74c3c;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>参加申請一覧</h1>
    <?php foreach ($requests as $request): ?>
        <div class="request-card">
            <p><strong><?php echo htmlspecialchars($request['user_name']); ?></strong> さんがグループ「<?php echo htmlspecialchars($request['group_name']); ?>」に参加申請しています。</p>
            <form method="POST" action="manage_group_request.php" style="display: inline;">
                <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                <input type="hidden" name="action" value="accept">
                <button type="submit">承諾する</button>
            </form>
            <form method="POST" action="manage_group_request.php" style="display: inline;">
                <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                <input type="hidden" name="action" value="reject">
                <button type="submit" class="reject">拒否する</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>
    <?php require 'footer.php'; // 共通フッター ?>
</body>
</html>
