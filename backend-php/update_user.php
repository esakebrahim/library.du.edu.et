<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Include the database connection file
include 'database.php'; // Ensure this path is correct

// Fetch all users to display their IDs
$user_query = "SELECT id, name, email, type FROM users";
$user_result = $conn->query($user_query);

// Initialize an array for storing users
$users = [];
while ($row = $user_result->fetch_assoc()) {
    $users[] = $row;
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $id = $_POST['id']; // User ID to update
    $name = $_POST['name'];
    $password = $_POST['password']; // New password if provided
    $type = $_POST['type'];

    // Prepare the SQL statement
    if (!empty($password)) {
        // If password is provided, update it (plain text password)
        $stmt = $conn->prepare("UPDATE users SET name = ?, password = ?, type = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $password, $type, $id);
    } else {
        // If no new password is provided, don't update the password
        $stmt = $conn->prepare("UPDATE users SET name = ?, type = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $type, $id);
    }

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect back to the dashboard with a success message
        header("Location: ../frontend/pages/admin/dashboard.html?msg=User updated successfully");
    } else {
        // Redirect back with an error message
        header("Location: ../frontend/pages/admin/dashboard.html?msg=Error updating user: " . $stmt->error);
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
    exit; // Stop further execution
}
?>