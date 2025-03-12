<?php
session_start();
include_once 'database.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['id']; // Get logged-in user's ID
    $recipient_id = $_POST['recipient_id'];
    $message = $_POST['message'];

    // Prepare and execute SQL to insert message
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $sender_id, $recipient_id, $message);
    $stmt->execute();
}
?>