<?php
session_start();
include_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verification_code = trim($_POST['verification_code']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    $redirect_url = '../frontend/reset_password.html';
    $login_url = '../frontend/login.html';

    // Validate inputs
    if (empty($verification_code) || empty($new_password) || empty($confirm_password)) {
        header("Location: " . $redirect_url . "?error=" . urlencode("All fields are required"));
        exit();
    }

    if ($new_password !== $confirm_password) {
        header("Location: " . $redirect_url . "?error=" . urlencode("Passwords do not match"));
        exit();
    }

    if (strlen($new_password) < 3) {
        header("Location: " . $redirect_url . "?error=" . urlencode("Password must be at least 3 characters long"));
        exit();
    }

    // Check if verification code exists and is valid
    $stmt = $conn->prepare("SELECT * FROM users WHERE verification_code = ?");
    $stmt->bind_param("s", $verification_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: " . $redirect_url . "?error=" . urlencode("Invalid verification code"));
        exit();
    }

    $user = $result->fetch_assoc();
    
    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password and clear verification code
    $update_stmt = $conn->prepare("UPDATE users SET password = ?, verification_code = NULL WHERE verification_code = ?");
    $update_stmt->bind_param("ss", $hashed_password, $verification_code);
    
    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = true; // Set a flag to show the message
        header("Location: " . $login_url . "?success=" . urlencode("Your password has been reset successfully! You can now login with your new password."));
        exit();
    } else {
        header("Location: " . $redirect_url . "?error=" . urlencode("Failed to reset password. Please try again."));
        exit();
    }
} else {
    header("Location: " . $redirect_url . "?error=" . urlencode("Invalid request method"));
    exit();
}

// Close the database connection
$conn->close();
?>
