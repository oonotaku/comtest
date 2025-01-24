<?php
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

try {
    // グループ情報を取得
    $sql = "SELECT * FROM user_groups WHERE id = :group_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':group_id' => $groupId]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$group) {
        echo "グループが存在しません。";
        exit;
    }

    // グループメンバー情報を取得
    $sql = "SELECT gm.user_id, r.name AS user_name, gm.role 
            FROM group_members gm 
            JOIN registrations r ON gm.user_id = r.id 
            WHERE gm.group_id = :group_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':group_id' => $groupId]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ログインユーザーID
    $userId = $_SESSION['user_id'];

    // 参加申請状況を確認
    $checkRequestSql = "SELECT status 
                        FROM group_requests 
                        WHERE group_id = :group_id AND user_id = :user_id";
    $checkRequestStmt = $pdo->prepare($checkRequestSql);
    $checkRequestStmt->execute([':group_id' => $groupId, ':user_id' => $userId]);
    $requestStatus = $checkRequestStmt->fetch(PDO::FETCH_ASSOC);

    // ユーザーがグループの管理者かどうか
    $isAdmin = false;
    foreach ($members as $member) {
        if ($member['user_id'] == $userId && $member['role'] === 'admin') {
            $isAdmin = true;
            break;
        }
    }

} catch (PDOException $e) {
    // エラー発生時にエラーメッセージを表示
    echo "エラーが発生しました: " . htmlspecialchars($e->getMessage());
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>グループ詳細</title>
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
        .description {
            margin-bottom: 20px;
        }
        .member-list {
            margin-top: 20px;
        }
        .member-card {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .member-card:last-child {
            border-bottom: none;
        }
        .btn {
            display: block;
            background-color: #ff7f50;
            color: white;
            padding: 10px 20px;
            text-align: center;
            border-radius: 4px;
            text-decoration: none;
            margin: 20px auto;
        }
        .btn:hover {
            background-color: #ff6347;
        }
        .disabled-btn {
            background-color: grey;
            cursor: not-allowed;
        }
        .invite-form {
            margin: 20px 0;
        }
        .invite-options {
            display: flex;
            flex-wrap: wrap;
        }
        .invite-option {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($group['name']); ?> の詳細</h1>
        <p class="description"><?php echo nl2br(htmlspecialchars($group['description'])); ?></p>

        <?php if ($isAdmin): ?>
            <p style="text-align: center; color: green; font-weight: bold;">あなたはこのグループの主催者です。</p>
            <!-- 招待機能 -->
            <form method="POST" action="group_invite.php" class="invite-form">
                <h3>ユーザーを招待する</h3>
                <div class="invite-options">
                    <?php
                    // 招待可能なユーザーを取得
                    $sql = "SELECT id, name FROM registrations WHERE id NOT IN (
                                SELECT user_id FROM group_members WHERE group_id = :group_id
                            )";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':group_id' => $groupId]);
                    $inviteUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($inviteUsers as $inviteUser): ?>
                        <label class="invite-option">
                            <input type="checkbox" name="user_ids[]" value="<?php echo $inviteUser['id']; ?>">
                            <?php echo htmlspecialchars($inviteUser['name']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="group_id" value="<?php echo $groupId; ?>">
                <button type="submit" class="btn">招待を送る</button>
            </form>
        <?php else: ?>
            <!-- 参加申請ボタンまたは申請中メッセージ -->
            <?php if ($requestStatus && $requestStatus['status'] === 'pending'): ?>
                <p style="color: orange; text-align: center;">申請中です。承認をお待ちください。</p>
            <?php elseif ($requestStatus && $requestStatus['status'] === 'rejected'): ?>
                <p style="color: red; text-align: center;">申請が拒否されました。</p>
            <?php elseif (!$requestStatus): ?>
                <form method="POST" action="send_group_request.php">
                    <input type="hidden" name="group_id" value="<?php echo $groupId; ?>">
                    <button type="submit" class="btn">グループに参加申請を送る</button>
                </form>
            <?php else: ?>
                <p style="color: green; text-align: center;">あなたはこのグループのメンバーです。</p>
            <?php endif; ?>
        <?php endif; ?>

        <!-- グループメンバー一覧 -->
        <div class="member-list">
            <h2>メンバー一覧</h2>
            <?php foreach ($members as $member): ?>
                <div class="member-card">
                    <a href="detail.php?id=<?php echo urlencode($member['user_id']); ?>" style="text-decoration: none; color: #007bff;">
                        <strong><?php echo htmlspecialchars($member['user_name']); ?></strong>
                    </a>
                    <?php if ($member['role'] === 'admin'): ?>
                        <span style="color: #ff7f50;">(管理者)</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>


        <p style="text-align: center;"><a href="group_list.php">グループ一覧に戻る</a></p>
    </div>
    <?php require 'footer.php'; // 共通フッター ?>
</body>
</html>
