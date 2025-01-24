<?php
require 'db.php';
session_start();

$currentUserId = $_SESSION['user_id'];
$partnerId = $_GET['partner_id'] ?? null;

// チャットメッセージを取得
$sql = "SELECT * FROM chat_messages 
        WHERE (sender_id = :current_user AND receiver_id = :partner_id) 
        OR (sender_id = :partner_id AND receiver_id = :current_user) 
        ORDER BY created_at ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':current_user' => $currentUserId, ':partner_id' => $partnerId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// メッセージを表示
foreach ($messages as $message) {
    $messageClass = $message['sender_id'] == $currentUserId ? 'sent' : 'received';
    echo "<div class='message $messageClass'><p>" . htmlspecialchars($message['message']) . "</p></div>";
}
