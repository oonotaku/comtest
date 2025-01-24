<?php
session_start();
require 'db.php';

// グループ情報を取得
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM groups WHERE created_by = :user_id OR EXISTS (SELECT * FROM group_members WHERE user_id = :user_id AND group_id = groups.id)");
$stmt->execute([':user_id' => $user_id]);
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// イベント作成処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_id = $_POST['group_id'] ?: null;
    $title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
    $event_date = $_POST['event_date'];
    $location = htmlspecialchars($_POST['location'], ENT_QUOTES, 'UTF-8');
    $fee = intval($_POST['fee']);
    $is_public = isset($_POST['is_public']) ? 1 : 0;

    $sql = "INSERT INTO events (group_id, title, description, event_date, location, fee, is_public, created_by) VALUES (:group_id, :title, :description, :event_date, :location, :fee, :is_public, :created_by)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':group_id' => $group_id,
        ':title' => $title,
        ':description' => $description,
        ':event_date' => $event_date,
        ':location' => $location,
        ':fee' => $fee,
        ':is_public' => $is_public,
        ':created_by' => $user_id,
    ]);

    $event_id = $pdo->lastInsertId();

    // 通知処理
    if ($is_public) {
        // 全ユーザーに通知
        $notification_message = "新しいイベント: {$title} が公開されました！";
    } else {
        // グループ内ユーザーに通知
        $notification_message = "グループイベント: {$title} に参加しましょう！";
    }

    header("Location: event_detail.php?event_id=" . $event_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>イベント作成</title>
</head>
<body>
    <h1>イベント作成</h1>
    <form method="POST" action="create_event.php">
        <label for="title">イベントタイトル:</label>
        <input type="text" name="title" id="title" required><br>

        <label for="description">イベント概要:</label>
        <textarea name="description" id="description" required></textarea><br>

        <label for="event_date">開催日時:</label>
        <input type="datetime-local" name="event_date" id="event_date" required><br>

        <label for="location">開催場所:</label>
        <input type="text" name="location" id="location" required><br>

        <label for="fee">参加費 (円):</label>
        <input type="number" name="fee" id="fee"><br>

        <label for="group_id">グループ選択:</label>
        <select name="group_id" id="group_id">
            <option value="">グループ外に告知</option>
            <?php foreach ($groups as $group): ?>
                <option value="<?php echo htmlspecialchars($group['id']); ?>"><?php echo htmlspecialchars($group['name']); ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="is_public">
            <input type="checkbox" name="is_public" id="is_public"> グループ外にも告知する
        </label><br>

        <button type="submit">イベントを作成する</button>
    </form>
        <?php require 'footer.php'; // 共通フッター ?>
</body>
</html>
