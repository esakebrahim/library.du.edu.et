<?php
session_start();
require_once '../../vendor/autoload.php';
use RobThree\Auth\TwoFactorAuth;

header('Content-Type: application/json');

// Check if user is logged in and get correct user ID
$user_id = null;
if (isset($_SESSION['student_id'])) {
    $user_id = $_SESSION['student_id'];
    $user_type = 'student';
} elseif (isset($_SESSION['teacher_id'])) {
    $user_id = $_SESSION['teacher_id'];
    $user_type = 'teacher';
} elseif (isset($_SESSION['librarian_id'])) {
    $user_id = $_SESSION['librarian_id'];
    $user_type = 'librarian';
} elseif (isset($_SESSION['admin_id'])) {
    $user_id = $_SESSION['admin_id'];
    $user_type = 'admin';
}

if (!$user_id) {
    echo json_encode([
        'success' => false,
        'message' => 'No valid user ID found'
    ]);
    error_log("2FA Error: No valid user ID found in session");
    exit();
}

try {
    // Initialize 2FA
    $tfa = new TwoFactorAuth('Library System');
    
    // Generate new secret
    $secret = $tfa->createSecret();
    
    // Get user's email
    include_once '../../backend-php/database.php';
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        throw new Exception("User not found in database");
    }

    // Generate QR code with user type included in label
    $qrLabel = 'Library System:' . $user['email'] . ':' . $user_type;
    $qrCodeUrl = $tfa->getQRCodeImageAsDataUri($qrLabel, $secret);
    
    // Store secret and user info temporarily in session
    $_SESSION['temp_secret'] = $secret;
    $_SESSION['temp_user_type'] = $user_type;
    $_SESSION['temp_user_id'] = $user_id;
    
    // Store or update in database
    $stmt = $conn->prepare("INSERT INTO two_factor_auth (user_id, auth_method, secret_key, is_enabled) 
                          VALUES (?, 'app', ?, 0) 
                          ON DUPLICATE KEY UPDATE auth_method = 'app', secret_key = ?, is_enabled = 0");
    if (!$stmt) {
        throw new Exception("Database prepare error: " . $conn->error);
    }
    $stmt->bind_param("iss", $user_id, $secret, $secret);
    if (!$stmt->execute()) {
        throw new Exception("Failed to update database: " . $stmt->error);
    }
    
    echo json_encode([
        'success' => true,
        'qr_code' => $qrCodeUrl,
        'secret' => $secret,
        'user_type' => $user_type
    ]);
} catch (Exception $e) {
    error_log("2FA Error in generate_qr.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to generate QR code: ' . $e->getMessage()
    ]);
}
?> 