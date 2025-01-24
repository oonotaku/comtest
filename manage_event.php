<?php
require 'db.php';
$event_id = $_GET['event_id'];

// 参加者リスト取得
$stmt = $pdo->prepare("SELECT * FROM event_participants WHERE event_id = :event_id");
$stmt->execute([':event_id' => $event_id]);
$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 参加費合計を計算
$stmt = $pdo->prepare("SELECT SUM(fee) FROM event_participants WHERE event_id = :event_id AND status = 'accepted'");
$stmt->execute([':event_id' => $event_id]);
$total_fee = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>イベント管理</title>
</head>
<body>
    <h1>イベント管理ページ</h1>
    <h2>参加者一覧</h2>
    <ul>
        <?php foreach ($participants as $participant): ?>
            <li><?php echo htmlspecialchars($participant['user_id']); ?>: <?php echo htmlspecialchars($participant['status']); ?></li>
        <?php endforeach; ?>
    </ul>
    <p>参加費合計予測: <?php echo $total_fee; ?>円</p>
</body>
</html>
