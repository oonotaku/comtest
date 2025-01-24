<?php
require 'db.php'; // データベース接続
// session_start(); // セッション開始
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

// グループ情報を取得
$sql = "SELECT * FROM groups WHERE id = :group_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':group_id' => $groupId]);
$group = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$group) {
    echo "グループが存在しません。";
    exit;
}

// グループ作成者か確認
if ($group['created_by'] !== $_SESSION['user_id']) {
    echo "あなたはこのグループの管理者ではありません。";
    echo "（デバッグ用）現在のユーザーID：" . $_SESSION['user_id'] . " / グループ作成者ID：" . $group['created_by'];
    exit;
}


// 参加申請一覧を取得
$sql = "SELECT gr.id AS request_id, r.name AS user_name, gr.user_id, gr.status FROM group_requests gr
        JOIN registrations r ON gr.user_id = r.id
        WHERE gr.group_id = :group_id AND gr.status = 'pending'";
$stmt = $pdo->prepare($sql);
$stmt->execute([':group_id' => $groupId]);
$pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 承認処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve'])) {
    $requestId = $_POST['request_id'];
    $userId = $_POST['user_id'];

    // 承認してメンバーに追加
    $pdo->beginTransaction();
    $sqlApprove = "UPDATE group_requests SET status = 'accepted' WHERE id = :request_id";
    $stmtApprove = $pdo->prepare($sqlApprove);
    $stmtApprove->execute([':request_id' => $requestId]);

    $sqlAddMember = "INSERT INTO group_members (group_id, user_id, role) VALUES (:group_id, :user_id, 'member')";
    $stmtAddMember = $pdo->prepare($sqlAddMember);
    $stmtAddMember->execute([':group_id' => $groupId, ':user_id' => $userId]);
    $pdo->commit();

    header("Location: group_manage.php?group_id=$groupId");
    exit;
}

// 拒否処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject'])) {
    $requestId = $_POST['request_id'];

    $sqlReject = "UPDATE group_requests SET status = 'rejected' WHERE id = :request_id";
    $stmtReject = $pdo->prepare($sqlReject);
    $stmtReject->execute([':request_id' => $requestId]);

    header("Location: group_manage.php?group_id=$groupId");
    exit;
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>グループ管理</title>
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
        .request-card {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .request-card:last-child {
            border-bottom: none;
        }
        .btn-approve, .btn-reject {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-approve {
            background-color: #4caf50;
            color: white;
        }
        .btn-reject {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($group['name']); ?> の管理ページ</h1>
// 確認コード
echo "ログインユーザーID: " . $_SESSION['user_id'];
echo "グループ管理者ID: " . $group['created_by'];

        <h2>参加申請一覧</h2>
        <?php if (count($pendingRequests) > 0): ?>
            <?php foreach ($pendingRequests as $request): ?>
                <div class="request-card">
                    <span><?php echo htmlspecialchars($request['user_name']); ?> さんから参加申請があります。</span>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                        <input type="hidden" name="user_id" value="<?php echo $request['user_id']; ?>">
                        <button type="submit" name="approve" class="btn-approve">承認</button>
                        <button type="submit" name="reject" class="btn-reject">拒否</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; color: gray;">現在、申請はありません。</p>
        <?php endif; ?>

        <p style="text-align: center; margin-top: 20px;">
            <a href="group_detail.php?group_id=<?php echo $groupId; ?>" style="text-decoration: none; color: #ff7f50;">← グループ詳細に戻る</a>
        </p>
    </div>
        <?php require 'footer.php'; // 共通フッター ?>
</body>
</html>
