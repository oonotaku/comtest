<?php
require 'db.php';
$event_id = $_GET['event_id'];
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = :event_id");
$stmt->execute([':event_id' => $event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    echo "イベントが見つかりません。";
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>イベント詳細</title>
</head>
<body>
    <h1><?php echo htmlspecialchars($event['title']); ?> のイベント詳細</h1>
    <p><?php echo htmlspecialchars($event['description']); ?></p>
    <p>日時: <?php echo htmlspecialchars($event['event_date']); ?></p>
    <p>場所: <?php echo htmlspecialchars($event['location']); ?></p>
    <p>参加費: <?php echo htmlspecialchars($event['fee']); ?>円</p>
        <?php require 'footer.php'; // 共通フッター ?>
</body>
</html>
