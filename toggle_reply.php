<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$tweetId = $data['tweet_id'] ?? null;

if (!$tweetId) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// ロジックの実装 (必要に応じて修正)
?>
