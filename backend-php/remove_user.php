<?php
// Include the database connection file
require_once 'database.php'; // Adjust the path if necessary

if (isset($_POST['id'])) {
    $user_id = $conn->real_escape_string($_POST['id']);
    
    // Prepare the SQL statement to delete the user
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        echo "User removed successfully.";
    } else {
        echo "Error removing user: " . $stmt->error;
    }
    
    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();

// Redirect back to the view_users.php page
header("Location: view_users.php");
exit;
?>