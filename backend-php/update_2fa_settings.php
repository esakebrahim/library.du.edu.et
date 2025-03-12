<?php
session_start();
include_once 'database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['id'];
$enabled = isset($_POST['enabled']) ? (int)$_POST['enabled'] : 0;
$method = isset($_POST['method']) ? $_POST['method'] : '';

// Validate method if 2FA is being enabled
if ($enabled && !in_array($method, ['email', 'authenticator'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid authentication method']);
    exit();
}

try {
    // Check if user already has 2FA settings
    $check_stmt = $conn->prepare("SELECT id FROM two_factor_auth WHERE user_id = ?");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing settings
        $stmt = $conn->prepare("UPDATE two_factor_auth SET is_enabled = ?, auth_method = ? WHERE user_id = ?");
        $stmt->bind_param("isi", $enabled, $method, $user_id);
    } else {
        // Insert new settings
        $stmt = $conn->prepare("INSERT INTO two_factor_auth (user_id, is_enabled, auth_method) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $enabled, $method);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '2FA settings updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update 2FA settings']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}

$conn->close();
?> 