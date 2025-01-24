<?php
require 'db.php'; // データベース接続ファイル
require 'header.php'; // 共通ヘッダー

// GETパラメータから 'id' を取得
$userId = intval($_GET['id'] ?? 0);

// ユーザーIDが正しいか確認
if ($userId === 0) {
    echo "エラー: URL に id が含まれていません。";
    exit;
}

// データベースから該当するユーザー情報を取得
$sql = "SELECT * FROM registrations WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $userId]);
$details = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$details) {
    echo "該当するユーザーが見つかりません。";
    exit;
}

// 現在ログイン中のユーザー
$currentUserId = $_SESSION['user_id'] ?? null;

// 自分自身かどうかを確認
$isCurrentUser = ($userId === $currentUserId);

// ユーザーのツイートを取得
$tweetSql = "SELECT * FROM tweets WHERE user_id = :user_id ORDER BY created_at DESC";
$tweetStmt = $pdo->prepare($tweetSql);
$tweetStmt->execute([':user_id' => $userId]);
$tweets = $tweetStmt->fetchAll(PDO::FETCH_ASSOC);

// フォロー状態を確認
$isFollowing = false; // 初期値
if ($currentUserId !== null) { // ログインしている場合のみ確認
    $followCheck = $pdo->prepare("SELECT 1 FROM follows WHERE follower_id = :currentUserId AND followed_id = :userId");
    $followCheck->execute([':currentUserId' => $currentUserId, ':userId' => $userId]);
    $isFollowing = $followCheck->fetchColumn();
}

// チャットリクエストの状態を確認
$requestStatus = null; // 初期値
if ($currentUserId !== null) { // ログインしている場合のみ確認
    $requestCheck = $pdo->prepare("SELECT status FROM chat_requests WHERE sender_id = :currentUserId AND receiver_id = :userId");
    $requestCheck->execute([':currentUserId' => $currentUserId, ':userId' => $userId]);
    $requestStatus = $requestCheck->fetchColumn();
}

// チャットルームの状態を確認
$chatRoomId = null; // 初期値
if ($currentUserId !== null) { // ログインしている場合のみ確認
    $chatCheck = $pdo->prepare("SELECT id FROM chat_rooms WHERE (sender_id = :currentUserId AND receiver_id = :userId) OR (sender_id = :userId AND receiver_id = :currentUserId)");
    $chatCheck->execute([':currentUserId' => $currentUserId, ':userId' => $userId]);
    $chatRoomId = $chatCheck->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($details['name']); ?> の詳細</title>
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .tweets-container {
            max-width: 800px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .button {
            display: inline-block;
            margin: 10px;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
            font-size: 1rem;
        }
        .follow { background-color: #007bff; }
        .unfollow { background-color: red; }
        .chat { background-color: green; }
        .disabled { background-color: gray; pointer-events: none; }
        img { max-width: 100%; border-radius: 8px; margin: 10px 0; }
    </style>
    <script>
        async function toggleFollow(userId) {
            const response = await fetch('toggle_follow.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId })
            });

            if (response.ok) {
                location.reload(); // 状態を更新
            } else {
                alert('フォロー処理に失敗しました');
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($details['name']); ?> の詳細</h1>
        <?php if (!empty($details['photo_path'])): ?>
            <img 
                src="<?php echo htmlspecialchars($details['photo_path']); ?>" 
                alt="<?php echo htmlspecialchars($details['name']); ?>" 
                style="width: 100%; max-width: 300px; height: auto; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); display: block; margin: 0 auto;">
        <?php endif; ?>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($details['email']); ?></p>
        <p><strong>会社:</strong> <?php echo htmlspecialchars($details['company']); ?></p>
        <p><strong>役職:</strong> <?php echo htmlspecialchars($details['position']); ?></p>
        <p><strong>備考:</strong> <?php echo htmlspecialchars($details['memo']); ?></p>
        <?php if ($isCurrentUser): ?>
            <a href="edit.php?id=<?php echo $userId; ?>" class="button chat">情報を編集する</a>
        <?php else: ?>
            <!-- フォローボタン -->
            <button class="button <?php echo $isFollowing ? 'unfollow' : 'follow'; ?>" onclick="toggleFollow(<?php echo $userId; ?>)">
                <?php echo $isFollowing ? 'フォロー解除' : 'フォローする'; ?>
            </button>
            
            <!-- チャットボタン -->
            <?php if ($chatRoomId): ?>
                <a href="chat_room.php?room_id=<?php echo $chatRoomId; ?>" class="button chat">チャットする</a>
            <?php elseif ($requestStatus === 'pending'): ?>
                <button class="button disabled">申請中</button>
            <?php else: ?>
                <a href="send_request.php?receiver_id=<?php echo $userId; ?>" class="button chat">チャット申請する</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="tweets-container">
        <h2><?php echo htmlspecialchars($details['name']); ?> のツイート</h2>
        <?php foreach ($tweets as $tweet): ?>
            <div class="tweet">
                <p style="font-size: 0.9rem; color: #999;"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($tweet['created_at']))); ?></p>
                <p onclick="window.location.href='tweet_detail.php?tweet_id=<?php echo $tweet['id']; ?>'" style="cursor: pointer;"><?php echo nl2br(htmlspecialchars($tweet['content'])); ?></p>
                <?php if (!empty($tweet['image_path'])): ?>
                    <img 
                        src="<?php echo htmlspecialchars($tweet['image_path']); ?>" 
                        alt="Tweet Image"
                        onclick="window.location.href='tweet_detail.php?tweet_id=<?php echo $tweet['id']; ?>'"
                        style="height: auto; border-radius: 10px; cursor: pointer;">
                <?php endif; ?>
                <hr>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
