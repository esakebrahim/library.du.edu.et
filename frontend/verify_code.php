<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
include_once '../backend-php/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['pending_email'];
    $entered_code = trim($_POST['verification_code']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate fields
    if (empty($entered_code) || empty($new_password) || empty($confirm_password)) {
        echo "<script>alert('All fields are required!'); window.history.back();</script>";
        exit();
    }

    // Check if passwords match
    if ($new_password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit();
    }

    // Check the verification code in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND verification_code = ?");
    $stmt->bind_param("si", $email, $entered_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the user's password
        $stmt = $conn->prepare("UPDATE users SET password = ?, verification_code = NULL WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        if ($stmt->execute()) {
            echo "<script>alert('Your password has been reset successfully!'); window.location.href = 'login.html';</script>";
        } else {
            echo "<script>alert('Error resetting password. Please try again.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Invalid verification code!'); window.history.back();</script>";
    }
}

// Close the database connection
$conn->close();
?>
