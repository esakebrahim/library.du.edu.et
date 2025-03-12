<?php
// Include the database connection
include '../../../backend-php/database.php';

// Fetch all messages with file paths
$query = "SELECT * FROM messages"; // Adjust this to your actual query
$result = mysqli_query($conn, $query);

$messages = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Get the relative file path from the database
    $filePath = $row['file_path'];

    $messages[] = [
        'id' => $row['id'],
        'message' => $row['message'],
        'file_path' => $filePath, // Store relative file path
        'created_at' => $row['created_at']
    ];
}

echo json_encode($messages);
?>
