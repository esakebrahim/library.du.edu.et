<?php
session_start();
include_once '../backend-php/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json');

// Log function for debugging
function logDebug($message, $data = null) {
    $log = date('Y-m-d H:i:s') . " - " . $message;
    if ($data !== null) {
        $log .= " - Data: " . print_r($data, true);
    }
    error_log($log);
}

try {
    // Get and log raw POST data
    $raw_post_data = file_get_contents('php://input');
    logDebug("Received raw data", $raw_post_data);
    
    $data = json_decode($raw_post_data, true);
    logDebug("Decoded data", $data);

    if (!$data || !isset($data['credential']) || !isset($data['type'])) {
        throw new Exception('Missing required data (credential or type)');
    }

    // Validate user type
    $allowed_types = ['student', 'teacher', 'librarian', 'admin'];
    if (!in_array($data['type'], $allowed_types)) {
        throw new Exception('Invalid user type');
    }

    // Decode the JWT token
    $jwt_parts = explode('.', $data['credential']);
    if (count($jwt_parts) != 3) {
        throw new Exception('Invalid credential format');
    }

    // Decode payload
    $payload = json_decode(base64_decode($jwt_parts[1]), true);
    logDebug("Decoded payload", $payload);
    
    if (!$payload) {
        throw new Exception('Invalid token payload');
    }

    // Extract user information
    $email = $payload['email'] ?? '';
    $name = $payload['name'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Check database connection
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Check if user already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Failed to prepare user check query: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute user check query: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        throw new Exception('Email already registered. Please login instead.');
    }

    // Create new user
    $insert = $conn->prepare("INSERT INTO users (name, email, type, status, is_confirmed, password) VALUES (?, ?, ?, 'approved', 1, '')");
    if (!$insert) {
        throw new Exception('Failed to prepare insert query: ' . $conn->error);
    }

    $insert->bind_param("sss", $name, $email, $data['type']);
    if (!$insert->execute()) {
        throw new Exception('Failed to create user: ' . $insert->error);
    }

    $user_id = $insert->insert_id;

    // Set session data
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_type'] = $data['type'];
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;

    // Get redirect path based on user type
    switch ($data['type']) {
        case 'student':
            $redirect = "pages/student/First Page.php";
            break;
        case 'teacher':
            $redirect = "pages/teacher/dashboard.php";
            break;
        case 'librarian':
            $redirect = "pages/librarian/dashboard.php";
            break;
        case 'admin':
            $redirect = "pages/admin/dashboard.php";
            break;
        default:
            throw new Exception('Invalid user type for redirect');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'redirect' => $redirect
    ]);

} catch (Exception $e) {
    logDebug("Error occurred", $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>