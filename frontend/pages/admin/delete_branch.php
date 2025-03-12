<?php
// Include the database connection file
include_once '../../../backend-php/database.php';

// Check if the branch ID is provided
if (isset($_GET['id'])) {
    $branch_id = intval($_GET['id']);

    // Prepare the delete statement
    $delete_query = "DELETE FROM library_branches WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $branch_id);

    // Execute the delete statement
    if ($stmt->execute()) {
        $message = "Branch deleted successfully.";
    } else {
        $message = "Error deleting branch: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
} else {
    $message = "No branch ID provided.";
}

// Close the database connection
$conn->close();

// Redirect to the view branches page with a message
header("Location: manage_branch.php?message=" . urlencode($message));
exit();
?>