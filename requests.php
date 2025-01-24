<?php
require 'db.php'; // データベース接続
require 'header.php'; // 共通ヘッダー

// ログイン確認
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// チャット申請を取得
$sql = "SELECT cr.id AS request_id, r.name AS requester_name, cr.status 
        FROM chat_requests cr 
        JOIN registrations r ON cr.sender_id = r.id 
        WHERE cr.receiver_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $userId]);
$chatRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// グループ招待を取得
$sql = "SELECT gr.id AS request_id, g.name AS group_name, r.name AS requester_name, gr.status, gr.group_id 
        FROM group_requests gr
        JOIN user_groups g ON gr.group_id = g.id
        JOIN registrations r ON gr.user_id = r.id
        WHERE gr.user_id = :user_id AND gr.status IN ('invited', 'accepted', 'rejected')";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $userId]);
$groupInvitations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// グループ申請を取得（自分が送った申請の状況）
$sql = "SELECT gr.id AS request_id, g.name AS group_name, gr.status, g.id AS group_id 
        FROM group_requests gr
        JOIN user_groups g ON gr.group_id = g.id
        WHERE gr.user_id = :user_id AND gr.status IN ('pending', 'accepted', 'rejected')";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $userId]);
$groupApplications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 申請の承諾/拒否処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestType = $_POST['request_type']; // 'chat', 'group_invite', 'group_application'
    $requestId = $_POST['request_id'];
    $action = $_POST['action']; // 'accept' または 'reject'

    if ($requestType === 'chat') {
        $sql = "UPDATE chat_requests SET status = :status WHERE id = :request_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':status' => $action === 'accept' ? 'accepted' : 'rejected',
            ':request_id' => $requestId,
        ]);

        if ($action === 'accept') {
            // チャットルームを作成
            $chatRequestSql = "SELECT sender_id, receiver_id FROM chat_requests WHERE id = :request_id";
            $chatRequestStmt = $pdo->prepare($chatRequestSql);
            $chatRequestStmt->execute([':request_id' => $requestId]);
            $chatRequest = $chatRequestStmt->fetch(PDO::FETCH_ASSOC);

            if ($chatRequest) {
                $chatRoomSql = "INSERT INTO chat_rooms (sender_id, receiver_id) VALUES (:sender, :receiver)";
                $chatRoomStmt = $pdo->prepare($chatRoomSql);
                $chatRoomStmt->execute([
                    ':sender' => $chatRequest['sender_id'],
                    ':receiver' => $chatRequest['receiver_id'],
                ]);
            }
        }
    } elseif ($requestType === 'group_invite' || $requestType === 'group_application') {
        $sql = "UPDATE group_requests SET status = :status WHERE id = :request_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':status' => $action === 'accept' ? 'accepted' : 'rejected',
            ':request_id' => $requestId,
        ]);

        if ($action === 'accept' && $requestType === 'group_invite') {
            $groupRequestSql = "SELECT group_id, user_id FROM group_requests WHERE id = :request_id";
            $groupRequestStmt = $pdo->prepare($groupRequestSql);
            $groupRequestStmt->execute([':request_id' => $requestId]);
            $groupRequest = $groupRequestStmt->fetch(PDO::FETCH_ASSOC);

            if ($groupRequest) {
                $insertMemberSql = "INSERT INTO group_members (group_id, user_id, role) VALUES (:group_id, :user_id, 'member')";
                $insertMemberStmt = $pdo->prepare($insertMemberSql);
                $insertMemberStmt->execute([
                    ':group_id' => $groupRequest['group_id'],
                    ':user_id' => $groupRequest['user_id'],
                ]);
            }
        }
    }

    header('Location: requests.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>申請管理</title>
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
        .request-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #fff7e6;
        }
        .btn {
            padding: 10px;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-accept {
            background-color: #4CAF50;
        }
        .btn-reject {
            background-color: #f44336;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>申請管理</h1>

        <!-- チャット申請 -->
        <h2>チャット申請</h2>
        <?php if (!empty($chatRequests)): ?>
            <?php foreach ($chatRequests as $request): ?>
                <div class="request-card">
                    <p><?php echo htmlspecialchars($request['requester_name']); ?> さんからのチャット申請</p>
                    <p>ステータス: <?php echo htmlspecialchars($request['status']); ?></p>
                    <?php if ($request['status'] === 'pending'): ?>
                        <form method="post">
                            <input type="hidden" name="request_type" value="chat">
                            <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                            <button type="submit" name="action" value="accept" class="btn btn-accept">承諾</button>
                            <button type="submit" name="action" value="reject" class="btn btn-reject">拒否</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>現在、チャット申請はありません。</p>
        <?php endif; ?>

        <!-- グループ招待 -->
        <h2>グループ招待</h2>
        <?php if (!empty($groupInvitations)): ?>
            <?php foreach ($groupInvitations as $invite): ?>
                <div class="request-card">
                    <p><?php echo htmlspecialchars($invite['group_name']); ?> に招待されています。</p>
                    <p>ステータス: <?php echo htmlspecialchars($invite['status']); ?></p>
                    <?php if ($invite['status'] === 'invited'): ?>
                        <form method="post">
                            <input type="hidden" name="request_type" value="group_invite">
                            <input type="hidden" name="request_id" value="<?php echo $invite['request_id']; ?>">
                            <button type="submit" name="action" value="accept" class="btn btn-accept">承諾</button>
                            <button type="submit" name="action" value="reject" class="btn btn-reject">拒否</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>現在、グループ招待はありません。</p>
        <?php endif; ?>

        <!-- グループ申請 -->
        <h2>グループ申請</h2>
        <?php if (!empty($groupApplications)): ?>
            <?php foreach ($groupApplications as $application): ?>
                <div class="request-card">
                    <p><?php echo htmlspecialchars($application['group_name']); ?> への申請</p>
                    <p>ステータス: <?php echo htmlspecialchars($application['status']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>現在、グループ申請はありません。</p>
        <?php endif; ?>
    </div>
</body>
</html>
