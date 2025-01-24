<?php
require 'db.php'; // データベース接続
require 'header.php'; // 共通ヘッダー
// session_start();

// URLにroom_idまたはuser_idがあるか確認
$roomId = $_GET['room_id'] ?? null;
$userId = $_GET['user_id'] ?? null;

if (!$roomId && $userId) {
    // `user_id`からルーム情報を取得
    $sql = "SELECT id, sender_id, receiver_id FROM chat_rooms 
            WHERE (sender_id = :user_id AND receiver_id = :partner_id) 
            OR (sender_id = :partner_id AND receiver_id = :user_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':partner_id' => $userId
    ]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($room) {
        // `room_id`が見つかったらリダイレクト
        header("Location: chat_room.php?room_id=" . $room['id']);
        exit;
    } else {
        echo "<p style='text-align: center; color: red;'>チャットルームが存在しません。</p>";
        exit;
    }
}

// `room_id`がある場合の通常処理
if (!$roomId) {
    echo "<p style='text-align: center; color: red;'>チャットルームが存在しません。</p>";
    exit;
}

// 相手の名前を取得
$sql = "SELECT sender_id, receiver_id FROM chat_rooms WHERE id = :room_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':room_id' => $roomId]);
$roomInfo = $stmt->fetch(PDO::FETCH_ASSOC);

$partnerId = $roomInfo['sender_id'] == $_SESSION['user_id'] ? $roomInfo['receiver_id'] : $roomInfo['sender_id'];

$sql = "SELECT name FROM registrations WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $partnerId]);
$partnerName = $stmt->fetchColumn();

// チャットメッセージの取得
$sql = "SELECT * FROM chat_messages WHERE room_id = :room_id ORDER BY created_at ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':room_id' => $roomId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// メッセージ送信処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');

    if (!empty($message)) {
        $sql = "INSERT INTO chat_messages (room_id, sender_id, receiver_id, message) VALUES (:room_id, :sender_id, :receiver_id, :message)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':room_id' => $roomId,
            ':sender_id' => $_SESSION['user_id'],
            ':receiver_id' => $partnerId,
            ':message' => $message
        ]);

        // ページリロードして最新メッセージを表示
        header("Location: chat_room.php?room_id=" . $roomId);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($partnerName); ?> とのトーク</title>
    <style>
        body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            background-color: #fff7e6;
        }
        .chat-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 0 20px 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .chat-header {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            background-color: #ff7f50;
            color: white;
            padding: 10px;
            position: sticky;
            top: 0;  /* 上部に固定 */
            z-index: 10;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .messages-container {
            max-height: 400px;
            overflow-y: auto;
            padding: 10px 0;
        }
        .message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 8px;
            max-width: 70%;
        }
        .message.sender {
            background-color: #ff7f50;
            color: white;
            text-align: right;
            margin-left: auto;
        }
        .message.receiver {
            background-color: #f1f1f1;
            text-align: left;
        }
        .message-input {
            display: flex;
            margin-top: 10px;
        }
        .message-input input[type="text"] {
            flex: 1;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .message-input button {
            background-color: #ff7f50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 5px;
        }
    </style>
</head>
<body>

<div class="chat-container">
    <!-- 固定されるヘッダー -->
    <h2 class="chat-header"><?php echo htmlspecialchars($partnerName); ?> とのトーク</h2>

    <!-- メッセージ一覧部分 -->
    <div class="messages-container">
        <?php if (count($messages) > 0): ?>
            <?php foreach ($messages as $msg): ?>
                <div class="message <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'sender' : 'receiver'; ?>">
                    <p><?php echo htmlspecialchars($msg['message']); ?></p>
                    <span style="font-size: 0.8rem;"><?php echo htmlspecialchars($msg['created_at']); ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; color: #666;">メッセージはまだありません。</p>
        <?php endif; ?>
    </div>

    <!-- メッセージ入力フォーム -->
    <form method="POST" class="message-input">
        <input type="text" name="message" placeholder="メッセージを入力..." required>
        <button type="submit">送信</button>
    </form>
</div>

<?php require 'footer.php'; ?>
</body>
</html>
