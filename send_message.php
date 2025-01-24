<?php
require 'db.php';
session_start();

$currentUserId = $_SESSION['user_id'];
$roomId = $_POST['room_id'];
$message = htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');

$sql = "INSERT INTO chat_messages (room_id, sender_id, receiver_id, message) VALUES (:room_id, :sender_id, :receiver_id, :message)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':room_id' => $roomId,
    ':sender_id' => $currentUserId,
    ':receiver_id' => $_POST['receiver_id'],
    ':message' => $message
]);
