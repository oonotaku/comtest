<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "ログインしてください。";
    exit;
}

$requestId = $_POST['request_id'];
$response = $_POST['response']; // "accept" または "reject"

$sql = "UPDATE chat_requests SET status = :status WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':status' => $response === 'accept' ? 'accepted' : 'rejected', ':id' => $requestId]);

if ($response === 'accept') {
    echo "チャットリクエストを承諾しました！";
} else {
    echo "チャットリクエストを拒否しました。";
}
header("Location: chat_requests.php");
exit;
