<?php
session_start();
include_once '../../backend-php/database.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Import required classes
use RobThree\Auth\TwoFactorAuth;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

try {
    require_once '../../vendor/autoload.php';
    
    // Check if the class exists
    if (!class_exists('RobThree\Auth\TwoFactorAuth')) {
        throw new Exception('TwoFactorAuth class not found. Please check your installation.');
    }
    
    // Initialize 2FA
    $tfa = new TwoFactorAuth('Library System');
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
}

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];
$user_name = $_SESSION['name'] ?? 'User';
$user_type = $_SESSION['type'] ?? '';

// Get current 2FA status from two_factor_auth table
$sql = "SELECT is_enabled, secret_key, created_at, auth_method FROM two_factor_auth WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$two_factor_settings = $result->fetch_assoc();

$two_factor_enabled = $two_factor_settings['is_enabled'] ?? 0;
$secret_key = $two_factor_settings['secret_key'] ?? '';
$auth_method = $two_factor_settings['auth_method'] ?? '';

// Get user's email from users table
$sql = "SELECT email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_email = $user['email'];

// Function to send email using PHPMailer
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
        $mail->Subject = 'Two-Factor Authentication Setup - Library System';
        
        // HTML email body
        $htmlBody = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #2563eb;'>Two-Factor Authentication Setup</h2>
            <p>Dear {$name},</p>
            <p>You have requested to enable two-factor authentication for your account.</p>
            <div style='background-color: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <p style='font-size: 18px; margin: 0;'>Your verification code is: <strong>{$code}</strong></p>
                <p style='color: #64748b; margin: 10px 0 0 0;'>This code will expire in 5 minutes.</p>
            </div>
            <p>If you did not request this, please ignore this email.</p>
            <p>Best regards,<br>Library System Team</p>
        </div>";

        $mail->Body = $htmlBody;
        $mail->AltBody = "Your verification code is: {$code}\nThis code will expire in 5 minutes.";

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['enable_2fa'])) {
        $auth_method = $_POST['auth_method'];
        
        if ($auth_method === 'app') {
            // Store the auth method in session
            $_SESSION['temp_auth_method'] = 'app';
            
            // Redirect to verification page
            header("Location: verify_2fa.php");
            exit();
        } elseif ($auth_method === 'email') {
            // Generate verification code for email
            $verification_code = sprintf('%06d', rand(0, 999999));
            $_SESSION['verification_code'] = $verification_code;
            $_SESSION['verification_expiry'] = time() + 300; // 5 minutes
            $_SESSION['temp_auth_method'] = 'email';
            
            // Send verification email
            if (sendVerificationEmail($user_email, $user_name, $verification_code)) {
                header("Location: verify_2fa.php");
                exit();
            } else {
                $error_message = "Failed to send verification code. Please try again.";
            }
        }
    } elseif (isset($_POST['disable_2fa'])) {
        $sql = "UPDATE two_factor_auth SET is_enabled = 0 WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $success_message = "Two-factor authentication has been disabled successfully.";
        } else {
            $error_message = "Failed to disable two-factor authentication.";
        }
    }
    
    // Refresh the page to show updated status
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --success: #059669;
            --danger: #dc2626;
            --warning: #d97706;
            --background: #f1f5f9;
            --surface: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--text);
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .card {
            background: var(--surface);
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .card-header {
            background: var(--surface);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            border-radius: 1rem 1rem 0 0 !important;
        }

        .card-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text);
        }

        .card-body {
            padding: 1.5rem;
        }

        .welcome-section {
            text-align: center;
            margin-bottom: 2rem;
        }

        .welcome-section h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.5rem;
        }

        .welcome-section p {
            color: var(--text-light);
            font-size: 1.1rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 500;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .status-badge.enabled {
            background: rgba(5, 150, 105, 0.1);
            color: var(--success);
        }

        .status-badge.disabled {
            background: rgba(220, 38, 38, 0.1);
            color: var(--danger);
        }

        .method-card {
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .method-card:hover {
            border-color: var(--primary);
            background: rgba(37, 99, 235, 0.05);
        }

        .method-card.selected {
            border-color: var(--primary);
            background: rgba(37, 99, 235, 0.1);
        }

        .method-card h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .method-card p {
            color: var(--text-light);
            margin-bottom: 0;
        }

        .method-card i {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            border: none;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-danger {
            background: var(--danger);
            border: none;
        }

        .btn-danger:hover {
            background: #b91c1c;
            transform: translateY(-1px);
        }

        .alert {
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .form-check {
            margin-bottom: 1rem;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome-section">
            <h1>Two-Factor Authentication Settings</h1>
            <p>Welcome, <?php echo htmlspecialchars($user_name); ?></p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2>Current Status</h2>
            </div>
            <div class="card-body">
                <div class="status-badge <?php echo $two_factor_enabled ? 'enabled' : 'disabled'; ?>">
                    <?php echo $two_factor_enabled ? 'Two-Factor Authentication Enabled' : 'Two-Factor Authentication Disabled'; ?>
                </div>

                <?php if ($two_factor_enabled): ?>
                    <p>Your current authentication method: <strong><?php echo ucfirst($auth_method); ?></strong></p>
                    <form method="POST" class="mt-4">
                        <button type="submit" name="disable_2fa" class="btn btn-danger">
                            <i class="fas fa-lock-open"></i> Disable Two-Factor Authentication
                        </button>
                    </form>
                <?php else: ?>
                    <form method="POST" class="mt-4">
                        <div class="mb-4">
                            <h3 class="mb-3">Select Authentication Method</h3>
                            <div class="method-card">
                                <i class="fas fa-envelope"></i>
                                <h3>Email Authentication</h3>
                                <p>Receive a verification code via email to your registered email address.</p>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="auth_method" id="email" value="email" required>
                                    <label class="form-check-label" for="email">
                                        Enable Email Authentication
                                    </label>
                                </div>
                            </div>

                            <div class="method-card">
                                <i class="fas fa-key"></i>
                                <h3>Authenticator App</h3>
                                <p>Use an authenticator app like Google Authenticator or Authy for enhanced security.</p>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="auth_method" id="app" value="app" required>
                                    <label class="form-check-label" for="app">
                                        Enable Authenticator App
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="enable_2fa" class="btn btn-primary">
                            <i class="fas fa-lock"></i> Enable Two-Factor Authentication
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Security Tips</h2>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-3">
                        <i class="fas fa-shield-alt text-primary me-2"></i>
                        Keep your authentication device secure and never share your verification codes.
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-key text-primary me-2"></i>
                        Use a strong password in combination with 2FA for maximum security.
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-mobile-alt text-primary me-2"></i>
                        Keep your phone number and email address up to date to ensure you can receive verification codes.
                    </li>
                    <li>
                        <i class="fas fa-user-shield text-primary me-2"></i>
                        If you lose access to your authentication method, contact the library administrator for assistance.
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add visual feedback when selecting authentication method
        document.querySelectorAll('.method-card').forEach(card => {
            card.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    document.querySelectorAll('.method-card').forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');
                }
            });
        });
    </script>
</body>
</html> 