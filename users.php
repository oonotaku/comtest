<?php
require 'header.php'; // 共通ヘッダー
require 'db.php'; // データベース接続

// ログインしているかを確認
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// ログインユーザーのID
$currentUserId = $_SESSION['user_id'];

// 検索キーワードを取得
$keyword = htmlspecialchars($_GET['keyword'] ?? '', ENT_QUOTES, 'UTF-8');

// SQLクエリを動的に生成
$sql = "SELECT id, name, photo_path, company FROM registrations WHERE id != :currentUserId";
$params = [':currentUserId' => $currentUserId];

if (!empty($keyword)) {
    $sql .= " AND company LIKE :keyword"; // 所属会社で部分一致検索
    $params[':keyword'] = '%' . $keyword . '%';
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// チャットルームが存在するか確認する関数
function chatRoomExists($pdo, $userId1, $userId2) {
    $sql = "SELECT id FROM chat_rooms WHERE (sender_id = :userId1 AND receiver_id = :userId2) OR (sender_id = :userId2 AND receiver_id = :userId1)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':userId1' => $userId1, ':userId2' => $userId2]);
    return $stmt->fetchColumn();
}

// 相手からの申請があるか確認する関数
function hasIncomingRequest($pdo, $currentUserId, $otherUserId) {
    $sql = "SELECT status FROM chat_requests WHERE sender_id = :otherUserId AND receiver_id = :currentUserId AND status = 'pending'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':otherUserId' => $otherUserId, ':currentUserId' => $currentUserId]);
    return $stmt->fetchColumn() === 'pending';
}

// 自分からの申請状態を確認する関数
function outgoingRequestStatus($pdo, $currentUserId, $otherUserId) {
    $sql = "SELECT status FROM chat_requests WHERE sender_id = :currentUserId AND receiver_id = :otherUserId";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':currentUserId' => $currentUserId, ':otherUserId' => $otherUserId]);
    return $stmt->fetchColumn();
}


?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Peatix風サイト</title>
  <style>

/* .center-message {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 70vh;
    text-align: center;
} */

/* .center-message h1 {
    font-size: 3rem;
    color: #ff7f50;
} */

.cards {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
    padding: 20px;
}

.card {
    width: calc(100% / 3 - 32px);
    max-width: 320px;
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: transform 0.3s ease-in-out;
    text-align: center; /* カード内の内容を中央寄せ */
}

.card:hover {
    transform: translateY(-10px);
}

.card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.card h3 {
    color: #ff7f50;
    font-size: 1.2rem;
    margin: 12px 0;
}

.card p {
    font-size: 0.9rem;
    color: #666;
    padding: 0 12px 12px;
}

.chat-link {
    display: block;
    text-align: center;
    background-color: #ff7f50;
    color: white;
    padding: 8px;
    text-decoration: none;
    border-radius: 4px;
    margin: 10px auto;
    width: 80%;
}

.chat-link:hover {
    background-color: #ff4500;
}

.pending-label {
    background-color: #ffdf85;
    color: black;
    padding: 6px 12px;
    border-radius: 4px;
    margin-top: 10px;
    display: inline-block;
}

.incoming-request-label {
    background-color: #ff6347;
    color: white;
    padding: 6px 12px;
    border-radius: 4px;
    margin-top: 10px;
}

.accepted-link {
    display: inline-block;
    text-align: center;
    padding: 8px 16px;
    background-color: #32cd32;
    color: white;
    border-radius: 4px;
    text-decoration: none;
}

.accepted-link:hover {
    background-color: #228b22;
}
        h1 {
            text-align: center;
            color: #ff7f50;
    </style>
</head>
<body>

<main>
    <h1 style="text-align: center; margin-top: 20px;">登録一覧</h1>

    <!-- 検索フォーム -->
    <div class="search-container" style="text-align: center; margin: 20px 0;">
        <form method="get" action="index.php">
            <input type="text" name="keyword" placeholder="会社名で検索" value="<?php echo htmlspecialchars($keyword); ?>" style="width: 300px; padding: 10px; font-size: 1rem; border: 1px solid #ccc; border-radius: 4px;">
            <button type="submit" style="padding: 10px 20px; background-color: #ff7f50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem;">検索</button>
        </form>
    </div>

    <div class="cards">
        <?php if (count($results) > 0): ?>
            <?php foreach ($results as $row): ?>
                <div class="card">
                    <!-- user_id を使用するように修正 -->
                    <a href="detail.php?id=<?php echo urlencode($row['id']); ?>">
                        <img src="<?php echo htmlspecialchars($row['photo_path']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    </a>

                    <p><?php echo htmlspecialchars($row['company']); ?></p>

                    <!-- チャット申請リンク -->
                    <?php
                    $chatRoomExists = chatRoomExists($pdo, $currentUserId, $row['id']);
                    $incomingRequest = hasIncomingRequest($pdo, $currentUserId, $row['id']);
                    $outgoingRequestStatus = outgoingRequestStatus($pdo, $currentUserId, $row['id']);

                    if ($chatRoomExists): ?>
                        <a href="chat_room.php?room_id=<?php echo $chatRoomExists; ?>" class="accepted-link">チャットする</a>
                    <?php elseif ($outgoingRequestStatus === 'pending'): ?>
                        <button disabled class="pending-button">申請中</button>
                    <?php elseif ($outgoingRequestStatus === 'rejected'): ?>
                        <a href="send_request.php?receiver_id=<?php echo urlencode($row['id']); ?>" class="chat-link">拒否（再申請）</a>
                    <?php elseif ($incomingRequest): ?>
                        <a href="requests.php" class="incoming-request-link">相手から申請が届いています</a>
                    <?php else: ?>
                        <a href="send_request.php?receiver_id=<?php echo urlencode($row['id']); ?>" class="chat-link">チャットを申請する</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; font-size: 1.2rem;">該当するデータがありません。</p>
        <?php endif; ?>
    </div>
</main>

<?php require 'footer.php'; // 共通フッター ?>

</body>
</html>
