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

// Function to get redirect path based on user type
function getRedirectPath($type) {
    switch ($type) {
        case 'student':
            return "pages/student/First Page.php";
        case 'teacher':
            return "pages/teacher/dashboard.php";
        case 'librarian':
            return "pages/librarian/dashboard.php";
        case 'admin':
            return "pages/admin/dashboard.php";
        default:
            throw new Exception('Invalid user type for redirect');
    }
}

try {
    // Get and log raw POST data
    $raw_post_data = file_get_contents('php://input');
    logDebug("Received raw data", $raw_post_data);
    
    $data = json_decode($raw_post_data, true);
    logDebug("Decoded data", $data);

    if (!$data || !isset($data['credential'])) {
        throw new Exception('Missing required data');
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

    // Check if user exists and get their type
    $stmt = $conn->prepare("SELECT id, type FROM users WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Failed to prepare user query: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute user query: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User exists - log them in
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $user['type'];
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;

        $redirect_path = getRedirectPath($user['type']);
        logDebug("Redirecting existing user", $redirect_path);

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => $redirect_path
        ]);
    } else {
        // User not found - they need to register first
        logDebug("User not found", $email);
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
    }

} catch (Exception $e) {
    logDebug("Error occurred", $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>