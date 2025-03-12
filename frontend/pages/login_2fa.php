<?php
session_start();
include_once '../../backend-php/database.php';
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Check if user has completed first step of login
if (!isset($_SESSION['pending_login_id'])) {
    header("Location: ../login.html");
    exit();
}

$user_id = $_SESSION['pending_login_id'];
$user_name = $_SESSION['pending_login_name'] ?? 'User';
$user_type = $_SESSION['pending_login_type'] ?? '';

// Get user's 2FA settings
$sql = "SELECT t.is_enabled, t.auth_method, t.secret_key, u.email 
        FROM two_factor_auth t 
        JOIN users u ON u.id = t.user_id 
        WHERE t.user_id = ? AND t.is_enabled = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$two_factor_settings = $result->fetch_assoc();

if (!$two_factor_settings || !$two_factor_settings['is_enabled']) {
    // If 2FA is not enabled, complete login
    $_SESSION['id'] = $user_id;
    $_SESSION['name'] = $user_name;
    $_SESSION['type'] = $user_type;
    
    // Set type-specific session variables
    switch ($user_type) {
        case 'student':
            $_SESSION['student_id'] = $user_id;
            $_SESSION['student_name'] = $user_name;
            $redirect = 'student/first page.php';
            break;
        case 'teacher':
            $_SESSION['teacher_id'] = $user_id;
            $_SESSION['teacher_name'] = $user_name;
            $redirect = 'teacher/dashboard.php';
            break;
        case 'librarian':
            $_SESSION['librarian_id'] = $user_id;
            $_SESSION['librarian_name'] = $user_name;
            $redirect = 'librarian/dashboard.php';
            break;
        case 'admin':
            $_SESSION['admin_id'] = $user_id;
            $_SESSION['admin_name'] = $user_name;
            $redirect = 'admin/dashboard.php';
            break;
        default:
            $redirect = '../login.html';
    }
    
    header("Location: $redirect");
    exit();
}

// Function to send verification email
function sendVerificationEmail($to, $name, $code) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'bekamgbdaw@gmail.com';
        $mail->Password = 'iznb palr txzi wfqe';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Recipients
        $mail->setFrom('bekamgbdaw@gmail.com', 'Library System');
        $mail->addAddress($to, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Login Verification Code - Library System';
        
        // HTML email body
        $htmlBody = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #2563eb;'>Login Verification Required</h2>
            <p>Dear {$name},</p>
            <p>A login attempt was made to your Library System account. To complete the login, please use the following verification code:</p>
            <div style='background-color: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <p style='font-size: 18px; margin: 0;'>Your verification code is: <strong>{$code}</strong></p>
                <p style='color: #64748b; margin: 10px 0 0 0;'>This code will expire in 5 minutes.</p>
            </div>
            <p>If you did not attempt to log in, please change your password immediately.</p>
            <p>Best regards,<br>Library System Team</p>
        </div>";

        $mail->Body = $htmlBody;
        $mail->AltBody = "Your login verification code is: {$code}\nThis code will expire in 5 minutes.";

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}

// Handle initial page load for email method
if ($two_factor_settings['auth_method'] === 'email' && !isset($_SESSION['verification_code'])) {
    // Generate and send verification code
    $verification_code = sprintf('%06d', rand(0, 999999));
    $_SESSION['verification_code'] = $verification_code;
    $_SESSION['verification_expiry'] = time() + 300; // 5 minutes

    if (!sendVerificationEmail($two_factor_settings['email'], $user_name, $verification_code)) {
        $error_message = "Failed to send verification code. Please try again.";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_code = trim($_POST['verification_code']);
    
    if ($two_factor_settings['auth_method'] === 'email') {
        $stored_code = $_SESSION['verification_code'] ?? '';
        $expiry_time = $_SESSION['verification_expiry'] ?? 0;
        
        if (strcmp($submitted_code, $stored_code) === 0 && time() < $expiry_time) {
            // Complete login
            $_SESSION['id'] = $user_id;
            $_SESSION['name'] = $user_name;
            $_SESSION['type'] = $user_type;
            
            // Set type-specific session variables
            switch ($user_type) {
                case 'student':
                    $_SESSION['student_id'] = $user_id;
                    $_SESSION['student_name'] = $user_name;
                    $redirect = 'student/first page.php';
                    break;
                case 'teacher':
                    $_SESSION['teacher_id'] = $user_id;
                    $_SESSION['teacher_name'] = $user_name;
                    $redirect = 'teacher/dashboard.php';
                    break;
                case 'librarian':
                    $_SESSION['librarian_id'] = $user_id;
                    $_SESSION['librarian_name'] = $user_name;
                    $redirect = 'librarian/dashboard.php';
                    break;
                case 'admin':
                    $_SESSION['admin_id'] = $user_id;
                    $_SESSION['admin_name'] = $user_name;
                    $redirect = 'admin/dashboard.php';
                    break;
                default:
                    $redirect = '../login.html';
            }
            
            // Clear 2FA session data
            unset($_SESSION['pending_login_id']);
            unset($_SESSION['pending_login_name']);
            unset($_SESSION['pending_login_type']);
            unset($_SESSION['verification_code']);
            unset($_SESSION['verification_expiry']);
            
            header("Location: $redirect");
            exit();
        } else {
            $error_message = "Invalid verification code or code has expired. Please try again.";
        }
    } elseif ($two_factor_settings['auth_method'] === 'app') {
        // TODO: Implement proper TOTP verification
        if (strcmp($submitted_code, $two_factor_settings['secret_key']) === 0) {
            // Complete login with same session setup as above
            $_SESSION['id'] = $user_id;
            $_SESSION['name'] = $user_name;
            $_SESSION['type'] = $user_type;
            
            switch ($user_type) {
                case 'student':
                    $_SESSION['student_id'] = $user_id;
                    $_SESSION['student_name'] = $user_name;
                    $redirect = 'student/first page.php';
                    break;
                case 'teacher':
                    $_SESSION['teacher_id'] = $user_id;
                    $_SESSION['teacher_name'] = $user_name;
                    $redirect = 'teacher/dashboard.php';
                    break;
                case 'librarian':
                    $_SESSION['librarian_id'] = $user_id;
                    $_SESSION['librarian_name'] = $user_name;
                    $redirect = 'librarian/dashboard.php';
                    break;
                case 'admin':
                    $_SESSION['admin_id'] = $user_id;
                    $_SESSION['admin_name'] = $user_name;
                    $redirect = 'admin/dashboard.php';
                    break;
                default:
                    $redirect = '../login.html';
            }
            
            unset($_SESSION['pending_login_id']);
            unset($_SESSION['pending_login_name']);
            unset($_SESSION['pending_login_type']);
            
            header("Location: $redirect");
            exit();
        } else {
            $error_message = "Invalid verification code. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Verification - Library System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient-start: #6366f1;
            --primary-gradient-end: #818cf8;
            --success-color: #22c55e;
            --error-color: #ef4444;
            --text-color: #1f2937;
        }

        body {
            background: linear-gradient(135deg, var(--primary-gradient-start), var(--primary-gradient-end));
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            margin: 0;
        }

        .container {
            max-width: 500px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            text-align: center;
        }

        .verification-icon {
            font-size: 3rem;
            background: linear-gradient(135deg, var(--primary-gradient-start), var(--primary-gradient-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            border: 2px solid rgba(99, 102, 241, 0.2);
            font-size: 1.1rem;
            text-align: center;
            letter-spacing: 0.5em;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus {
            border-color: var(--primary-gradient-start);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-gradient-start), var(--primary-gradient-end));
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 500;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }

        .alert {
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(5px);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--error-color);
        }

        #timer {
            text-align: center;
            margin-top: 1rem;
            color: var(--text-color);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <i class="fas <?php echo $two_factor_settings['auth_method'] === 'email' ? 'fa-envelope' : 'fa-key'; ?> verification-icon"></i>
                <h2 class="mb-0">Login Verification Required</h2>
            </div>
            <div class="card-body">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <div class="text-center mb-4">
                    <?php if ($two_factor_settings['auth_method'] === 'email'): ?>
                        <p>Please enter the verification code sent to your email address.</p>
                    <?php else: ?>
                        <p>Please enter the code from your authenticator app.</p>
                    <?php endif; ?>
                </div>

                <form method="POST">
                    <div class="mb-4">
                        <input type="text" class="form-control" name="verification_code" 
                               pattern="[0-9]{6}" maxlength="6" required 
                               placeholder="000000" autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Verify and Login
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($two_factor_settings['auth_method'] === 'email'): ?>
    <script>
        // Add countdown timer for email verification
        function updateTimer() {
            const expiryTime = <?php echo $_SESSION['verification_expiry'] ?? 0; ?>;
            const now = Math.floor(Date.now() / 1000);
            const timeLeft = expiryTime - now;
            
            const timerElement = document.createElement('div');
            timerElement.id = 'timer';
            
            if (timeLeft <= 0) {
                timerElement.innerHTML = '<span style="color: var(--error-color)">Code has expired. Please refresh to request a new code.</span>';
                document.querySelector('button[type="submit"]').disabled = true;
            } else {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                timerElement.innerHTML = `Time remaining: ${minutes}:${seconds.toString().padStart(2, '0')}`;
            }
            
            const existingTimer = document.getElementById('timer');
            if (existingTimer) {
                existingTimer.replaceWith(timerElement);
            } else {
                document.querySelector('form').appendChild(timerElement);
            }
        }

        // Update timer every second
        updateTimer();
        setInterval(updateTimer, 1000);
    </script>
    <?php endif; ?>
</body>
</html> 