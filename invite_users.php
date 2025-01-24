<?php
require 'db.php';
require 'header.php';

// ログイン確認
// session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$groupId = $_GET['group_id'] ?? null;
if (!$groupId) {
    echo "グループIDが指定されていません。";
    exit;
}

// 招待可能なユーザーを取得
$sql = "SELECT id, name FROM registrations 
        WHERE id NOT IN (
            SELECT user_id FROM group_members WHERE group_id = :group_id
        )";
$stmt = $pdo->prepare($sql);
$stmt->execute([':group_id' => $groupId]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー招待</title>
</head>
<body>
    <h1>ユーザー招待</h1>
    <form method="post" action="process_invitation.php">
        <input type="hidden" name="group_id" value="<?php echo htmlspecialchars($groupId); ?>">
        <?php foreach ($users as $user): ?>
            <div>
                <label>
                    <input type="checkbox" name="user_ids[]" value="<?php echo $user['id']; ?>">
                    <?php echo htmlspecialchars($user['name']); ?>
                </label>
            </div>
        <?php endforeach; ?>
        <button type="submit">招待を送信</button>
    </form>
</body>
</html>
