<?php
require 'db.php'; // データベース接続
require 'header.php'; // 共通ヘッダー

// ログイン中のユーザーIDを取得
$currentUserId = $_SESSION['user_id'] ?? null;

// 自分への申請を取得
$sql = "SELECT cr.id, r.name AS sender_name FROM chat_requests cr
        JOIN registrations r ON cr.sender_id = r.id
        WHERE cr.receiver_id = :receiver_id AND cr.status = 'pending'";
$stmt = $pdo->prepare($sql);
$stmt->execute([':receiver_id' => $currentUserId]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>チャット申請一覧</title>
</head>
<body>
    <h1>チャット申請一覧</h1>

    <?php if (count($requests) > 0): ?>
        <ul>
            <?php foreach ($requests as $request): ?>
                <li>
                    <?php echo htmlspecialchars($request['sender_name']); ?> さんからのチャット申請
                    <a href="accept_request.php?request_id=<?php echo $request['id']; ?>">承認する</a>
                    <a href="reject_request.php?request_id=<?php echo $request['id']; ?>">拒否する</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>現在、チャット申請はありません。</p>
    <?php endif; ?>
        <?php require 'footer.php'; // 共通フッター ?>
</body>
</html>
