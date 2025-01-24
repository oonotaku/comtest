<?php
require 'db.php'; // データベース接続

session_start();

// ユーザーがログインしているか確認
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('ログインしてください。'); window.location.href='login.php';</script>";
    exit;
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_GET['receiver_id'] ?? null;

// 必要なIDの確認
if (!$receiver_id) {
    echo "<script>alert('無効なリクエストです。'); window.location.href='index.php';</script>";
    exit;
}

// チャットリクエストが既に存在するか確認
$stmt = $pdo->prepare("SELECT * FROM chat_requests WHERE sender_id = :sender_id AND receiver_id = :receiver_id");
$stmt->execute([':sender_id' => $sender_id, ':receiver_id' => $receiver_id]);
$existingRequest = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existingRequest) {
    echo "<script>alert('既にチャット申請を送信済みです。'); window.location.href='index.php';</script>";
    exit;
}

// チャットリクエストをデータベースに挿入
try {
    $stmt = $pdo->prepare("INSERT INTO chat_requests (sender_id, receiver_id, status) VALUES (:sender_id, :receiver_id, 'pending')");
    $stmt->execute([':sender_id' => $sender_id, ':receiver_id' => $receiver_id]);
    echo "<script>alert('チャット申請を送信しました。'); window.location.href='index.php';</script>";
} catch (PDOException $e) {
    echo "<script>alert('エラーが発生しました: {$e->getMessage()}'); window.location.href='index.php';</script>";
}
