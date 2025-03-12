<?php
// Start the session
session_start();

// Include your database connection file
include_once 'database.php'; // Make sure this path is correct

// Check if the admin is logged in (implement your authentication check here)
// if (!isset($_SESSION['admin_logged_in'])) {
//     header("Location: login.php"); // Redirect to login if not logged in
//     exit();
// }

// Approve user
if (isset($_GET['approve'])) {
    $userId = intval($_GET['approve']); // Get user ID from URL and sanitize
    $sql = "UPDATE users SET status = 'approved' WHERE id = $userId"; // SQL query to update status
    
    if ($conn->query($sql) === TRUE) {
        // Redirect back to the approval page with a success message (optional)
        header("Location: ../frontend/pages/admin/settings.php?message=User approved successfully.");
        exit();
    } else {
        // Redirect back to the approval page with an error message (optional)
        header("Location: admin_approve.php?error=Error updating record: " . $conn->error);
        exit();
    }
}

// Close the database connection
$conn->close();
?>