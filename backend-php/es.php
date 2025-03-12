<?php
// Database connection
include_once 'database.php'; // Ensure this file connects to the database

// Define the new password
$new_password = "123";
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

try {
    // Start a transaction
    $conn->begin_transaction();

    // Update all users' passwords
    $updateQuery = "UPDATE users SET password = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("s", $hashed_password);

    if ($stmt->execute()) {
        echo "All passwords updated successfully!";
    } else {
        throw new Exception("Error updating passwords: " . $stmt->error);
    }

    // Commit the transaction
    $conn->commit();

} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    die("Transaction failed: " . $e->getMessage());
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>