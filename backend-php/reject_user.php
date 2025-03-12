<?php
// Start the session
session_start();

// Include your database connection file
include_once 'database.php'; // Ensure the path is correct

// Check if the admin is logged in (implement your authentication check here)
 if (!isset($_SESSION['admin_id'])) {
     header("Location: login.php"); // Redirect to login if not logged in
     exit();
 }

// Reject user (delete from the database)
if (isset($_GET['reject'])) {
    $userId = intval($_GET['reject']); // Get user ID from URL and sanitize

    // SQL query to delete the user
    $sql = "DELETE FROM users WHERE id = $userId";
    
    if ($conn->query($sql) === TRUE) {
        // Redirect back to the approval page with a success message (optional)
        header("Location: admin_approve.php?message=User rejected and removed successfully.");
        exit();
    } else {
        // Redirect back to the approval page with an error message (optional)
        header("Location: admin_approve.php?error=Error deleting record: " . $conn->error);
        exit();
    }
}

// Close the database connection
$conn->close();
?>