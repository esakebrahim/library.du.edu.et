<?php
// Include database connection
include_once '../../../backend-php/database.php';

// Validate input
if (!isset($_POST['librarian_id']) || !isset($_POST['role_id'])) {
    echo "Please select both a librarian and a role.";
    exit();
}

// Get and sanitize the librarian ID and role ID from the form
$librarian_id = filter_var($_POST['librarian_id'], FILTER_VALIDATE_INT);
$role_id = filter_var($_POST['role_id'], FILTER_VALIDATE_INT);

// Validate IDs
if ($librarian_id === false || $role_id === false) {
    echo "Invalid input data. Please try again.";
    exit();
}

// Verify librarian exists and is actually a librarian
$check_sql = "SELECT id FROM users WHERE id = ? AND type = 'librarian'";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $librarian_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    echo "Invalid librarian selected. Please try again.";
    $check_stmt->close();
    $conn->close();
    exit();
}
$check_stmt->close();

// Verify role exists
$check_role_sql = "SELECT id FROM librarian_roles WHERE id = ?";
$check_role_stmt = $conn->prepare($check_role_sql);
$check_role_stmt->bind_param("i", $role_id);
$check_role_stmt->execute();
$check_role_result = $check_role_stmt->get_result();

if ($check_role_result->num_rows === 0) {
    echo "Invalid role selected. Please try again.";
    $check_role_stmt->close();
    $conn->close();
    exit();
}
$check_role_stmt->close();

// Update the librarian's role in the database
$sql = "UPDATE users SET role_id = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $role_id, $librarian_id);

if ($stmt->execute()) {
    echo "Role assigned successfully!";
} else {
    echo "Error assigning role. Please try again.";
}

$stmt->close();
$conn->close();
?>