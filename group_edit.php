<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php'; // データベース接続
require 'header.php'; // 共通ヘッダー

// ログインしていない場合はリダイレクト
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// `group_id` を取得
$groupId = $_GET['group_id'] ?? null;
if (!$groupId) {
    echo "グループIDが指定されていません。";
    exit;
}

// ログインユーザーID
$userId = $_SESSION['user_id'];

try {
    // グループ情報を取得
    $sql = "SELECT * FROM `groups` WHERE id = :group_id AND created_by = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':group_id' => $groupId, ':user_id' => $userId]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$group) {
        echo "グループが存在しないか、編集権限がありません。";
        exit;
    }

    // フォーム送信処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';

        if (empty($name) || empty($description)) {
            echo "グループ名と説明を入力してください。";
        } else {
            $updateSql = "UPDATE `groups` SET name = :name, description = :description WHERE id = :group_id";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':group_id' => $groupId,
            ]);

            echo "グループ情報を更新しました。";
            header("Location: group_detail.php?group_id=$groupId");
            exit;
        }
    }
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
    <title>グループ編集</title>
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #ff7f50;
        }
        form {
            margin-top: 20px;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #ff7f50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #ff6347;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>グループ編集</h1>
        <form method="POST" action="">
            <label for="name">グループ名</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($group['name']); ?>" required>

            <label for="description">説明</label>
            <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($group['description']); ?></textarea>

            <button type="submit">更新する</button>
        </form>
    </div>
</body>
</html>
