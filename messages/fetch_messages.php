<?php
session_start();
include_once 'database.php'; // Include your database connection

$recipient_id = $_GET['recipient'];
$sender_id = $_SESSION['id'];

// Fetch messages between logged-in user and selected recipient
$stmt = $conn->prepare("
    SELECT * FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
    ORDER BY created_at ASC
");
$stmt->bind_param("iiii", $sender_id, $recipient_id, $recipient_id, $sender_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row; // Collect messages
}

echo json_encode($messages); // Return messages as JSON
?>