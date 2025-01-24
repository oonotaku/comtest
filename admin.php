<?php
// session_start();
require 'db.php';
require 'header.php'; // ログインユーザー名の表示を含む共通ヘッダー

// 管理者ログイン済みか確認
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php'); // 管理者ログインページにリダイレクト
    exit;
}

// 全ユーザーのデータを取得
$sql = "SELECT * FROM registrations";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];

    // データを削除
    $sql = "DELETE FROM registrations WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $deleteId]);

    echo "削除が完了しました！<br>";
    echo '<a href="admin.php">管理者ページに戻る</a>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者専用ページ</title>
</head>
<body>
    <h1>管理者専用ページ</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>名前</th>
            <th>Email</th>
            <th>所属会社</th>
            <th>役職</th>
            <th>操作</th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo htmlspecialchars($user['id']); ?></td>
            <td><?php echo htmlspecialchars($user['name']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo htmlspecialchars($user['company']); ?></td>
            <td><?php echo htmlspecialchars($user['position']); ?></td>
            <td>
                <a href="edit.php?id=<?php echo $user['id']; ?>">編集</a>
                <form method="post" action="admin.php" style="display:inline;">
                    <input type="hidden" name="delete_id" value="<?php echo $user['id']; ?>">
                    <button type="submit" onclick="return confirm('本当に削除しますか？')">削除</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <a href="logout.php">ログアウト</a>

</body>
</html>
