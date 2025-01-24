<?php
require 'db.php'; // データベース接続
require 'header.php'; // 共通ヘッダー

// ログインしていない場合はリダイレクト
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$groupId = $_GET['group_id'] ?? null;

if (!$groupId) {
    echo "グループIDが指定されていません。";
    exit;
}

// グループ情報を取得
try {
    $sql = "SELECT * FROM user_groups WHERE id = :group_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':group_id' => $groupId]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$group) {
        echo "グループが存在しません。";
        exit;
    }
} catch (PDOException $e) {
    echo "エラーが発生しました: " . htmlspecialchars($e->getMessage());
    exit;
}

// メッセージを送信
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];

    if (!empty($message)) {
        try {
            $sql = "INSERT INTO group_messages (group_id, sender_id, message) VALUES (:group_id, :sender_id, :message)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':group_id' => $groupId,
                ':sender_id' => $userId,
                ':message' => $message,
            ]);
        } catch (PDOException $e) {
            echo "メッセージ送信時にエラーが発生しました: " . htmlspecialchars($e->getMessage());
        }
    }
    // 再読み込みしてフォームの再送信を防止
    header("Location: group_chat_room.php?group_id=$groupId");
    exit;
}

// メッセージを取得
try {
    $sql = "SELECT gm.message, gm.created_at, r.name AS sender_name, gm.sender_id
            FROM group_messages gm
            JOIN registrations r ON gm.sender_id = r.id
            WHERE gm.group_id = :group_id
            ORDER BY gm.created_at ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':group_id' => $groupId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "チャットメッセージ取得時にエラーが発生しました: " . htmlspecialchars($e->getMessage());
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($group['name']); ?> のチャット</title>
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
        .chat-box {
            height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            background-color: #fff7e6;
            margin-bottom: 20px;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 8px;
            max-width: 70%;
        }
        .message.left {
            background-color: #f0f0f0;
            margin-left: 0;
        }
        .message.right {
            background-color: #d1ecf1;
            margin-left: auto;
        }
        .sender {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-group {
            display: flex;
            margin-top: 20px;
        }
        .form-group input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-group button {
            margin-left: 10px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($group['name']); ?> のチャット</h1>
        <div class="chat-box">
            <?php foreach ($messages as $message): ?>
                <?php 
                    $messageClass = ($message['sender_id'] == $userId) ? 'right' : 'left'; 
                ?>
                <div class="message <?php echo htmlspecialchars($messageClass); ?>">
                    <div class="sender"><?php echo htmlspecialchars($message['sender_name']); ?></div>
                    <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                    <small><?php echo htmlspecialchars($message['created_at']); ?></small>
                </div>
            <?php endforeach; ?>
        </div>
        <form method="POST" action="" class="form-group">
            <input type="text" name="message" placeholder="メッセージを入力してください" required>
            <button type="submit">送信</button>
        </form>
    </div>
    <script>
        // チャットボックスを自動スクロール
        document.addEventListener('DOMContentLoaded', function() {
            const chatBox = document.querySelector('.chat-box');
            chatBox.scrollTop = chatBox.scrollHeight;
        });
    </script>
</body>
</html>
