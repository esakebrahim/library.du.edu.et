<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the Database connection
include_once 'database.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get and sanitize input
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Validate input
    if (empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in all fields!'
        ]);
        exit;
    }

    // Prepare and execute SQL statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Check if user exists
    if ($row) {
        // Check if user is approved
        if ($row['status'] == "approved") {
            // Check password first
            if (password_verify($password, $row['password'])) {
                // Now check if account is verified
                if ($row['is_confirmed'] == 0) {
                    echo json_encode([
                        'success' => false,
                        'needsVerification' => true
                    ]);
                    exit();
                }

                // Check if user has 2FA enabled
                $stmt = $conn->prepare("SELECT is_enabled, auth_method FROM two_factor_auth WHERE user_id = ? AND is_enabled = 1");
                $stmt->bind_param("i", $row['id']);
                $stmt->execute();
                $two_factor_result = $stmt->get_result();
                $requires_2fa = $two_factor_result->num_rows > 0;

                if ($requires_2fa) {
                    // Store pending login information in session
                    $_SESSION['pending_login_id'] = $row['id'];
                    $_SESSION['pending_login_name'] = $row['name'];
                    $_SESSION['pending_login_type'] = $row['type'];
                    
                    echo json_encode([
                        'success' => true,
                        'requires_2fa' => true,
                        'redirect' => '../frontend/pages/login_2fa.php'
                    ]);
                    exit();
                }

                // No 2FA required, proceed with login
                $_SESSION['email'] = $email;
                $_SESSION['type'] = $row['type'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['id'] = $row['id'];

                $redirect = '';
                
                // Store user ID based on user type
                switch ($row['type']) {
                    case 'student':
                        $_SESSION['student_id'] = $row['id'];
                        $_SESSION['student_name'] = $row['name'];
                        $redirect = '../frontend/pages/student/first page.php';
                        break;

                    case 'teacher':
                        $_SESSION['teacher_id'] = $row['id'];
                        $_SESSION['teacher_name'] = $row['name'];
                        $redirect = '../frontend/pages/teacher/dashboard.php';
                        break;

                    case 'librarian':
                        $_SESSION['librarian_id'] = $row['id'];
                        $_SESSION['librarian_name'] = $row['name'];
                        $redirect = '../frontend/pages/librarian/dashboard.php';
                        break;

                    case 'admin':
                        $_SESSION['admin_id'] = $row['id'];
                        $_SESSION['admin_name'] = $row['name'];
                        $redirect = '../frontend/pages/admin/dashboard.php';
                        break;

                    default:
                        echo json_encode([
                            'success' => false,
                            'message' => 'User type not recognized.'
                        ]);
                        exit();
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful! Redirecting...',
                    'redirect' => $redirect
                ]);
                exit();
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid email or password!'
                ]);
                exit();
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Your account is not approved yet!'
            ]);
            exit();
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password!'
        ]);
        exit();
    }
}

// Close the database connection
$conn->close();
?>