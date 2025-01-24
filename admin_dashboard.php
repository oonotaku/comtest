<?php
require 'db.php';
session_start();

// 管理者チェック
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// ユーザごとのデータを取得
try {
    // チャット申請の集計
    $chatSql = "SELECT 
                    r.id AS user_id,
                    r.name AS user_name,
                    COUNT(CASE WHEN cr.status = 'pending' THEN 1 END) AS chat_pending,
                    COUNT(CASE WHEN cr.status = 'accepted' THEN 1 END) AS chat_accepted,
                    COUNT(CASE WHEN cr.status = 'rejected' THEN 1 END) AS chat_rejected
                FROM 
                    registrations r
                LEFT JOIN 
                    chat_requests cr ON r.id = cr.receiver_id
                GROUP BY 
                    r.id";
    $chatStmt = $pdo->prepare($chatSql);
    $chatStmt->execute();
    $chatData = $chatStmt->fetchAll(PDO::FETCH_ASSOC);

    // グループ招待の集計
    $groupInviteSql = "SELECT 
                            r.id AS user_id,
                            r.name AS user_name,
                            COUNT(CASE WHEN gr.status = 'invited' THEN 1 END) AS group_invited,
                            COUNT(CASE WHEN gr.status = 'accepted' THEN 1 END) AS group_accepted,
                            COUNT(CASE WHEN gr.status = 'rejected' THEN 1 END) AS group_rejected
                        FROM 
                            registrations r
                        LEFT JOIN 
                            group_requests gr ON r.id = gr.user_id
                        GROUP BY 
                            r.id";
    $groupInviteStmt = $pdo->prepare($groupInviteSql);
    $groupInviteStmt->execute();
    $groupInviteData = $groupInviteStmt->fetchAll(PDO::FETCH_ASSOC);

    // グループ申請の集計
    $groupApplicationSql = "SELECT 
                                r.id AS user_id,
                                r.name AS user_name,
                                COUNT(CASE WHEN gr.status = 'pending' THEN 1 END) AS group_applied,
                                COUNT(CASE WHEN gr.status = 'accepted' THEN 1 END) AS group_application_accepted,
                                COUNT(CASE WHEN gr.status = 'rejected' THEN 1 END) AS group_application_rejected
                            FROM 
                                registrations r
                            LEFT JOIN 
                                group_requests gr ON r.id = gr.user_id
                            WHERE 
                                gr.status IS NOT NULL
                            GROUP BY 
                                r.id";
    $groupApplicationStmt = $pdo->prepare($groupApplicationSql);
    $groupApplicationStmt->execute();
    $groupApplicationData = $groupApplicationStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "エラーが発生しました: " . htmlspecialchars($e->getMessage());
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者ダッシュボード</title>
    <style>
        .container {
            max-width: 900px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #ff7f50;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>管理者ダッシュボード</h1>

        <h2>チャット申請の集計</h2>
        <table>
            <tr>
                <th>ユーザ名</th>
                <th>申請中</th>
                <th>承諾</th>
                <th>拒否</th>
            </tr>
            <?php foreach ($chatData as $chat): ?>
                <tr>
                    <td><?php echo htmlspecialchars($chat['user_name']); ?></td>
                    <td><?php echo $chat['chat_pending']; ?></td>
                    <td><?php echo $chat['chat_accepted']; ?></td>
                    <td><?php echo $chat['chat_rejected']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2>グループ招待の集計</h2>
        <table>
            <tr>
                <th>ユーザ名</th>
                <th>招待中</th>
                <th>承諾</th>
                <th>拒否</th>
            </tr>
            <?php foreach ($groupInviteData as $invite): ?>
                <tr>
                    <td><?php echo htmlspecialchars($invite['user_name']); ?></td>
                    <td><?php echo $invite['group_invited']; ?></td>
                    <td><?php echo $invite['group_accepted']; ?></td>
                    <td><?php echo $invite['group_rejected']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2>グループ申請の集計</h2>
        <table>
            <tr>
                <th>ユーザ名</th>
                <th>申請中</th>
                <th>承諾</th>
                <th>拒否</th>
            </tr>
            <?php foreach ($groupApplicationData as $application): ?>
                <tr>
                    <td><?php echo htmlspecialchars($application['user_name']); ?></td>
                    <td><?php echo $application['group_applied']; ?></td>
                    <td><?php echo $application['group_application_accepted']; ?></td>
                    <td><?php echo $application['group_application_rejected']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
