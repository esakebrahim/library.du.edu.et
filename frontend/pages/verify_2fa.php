<?php
session_start();
require_once '../../vendor/autoload.php';
include_once '../../backend-php/database.php';

use RobThree\Auth\TwoFactorAuth;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Initialize 2FA
try {
    if (!class_exists('RobThree\Auth\TwoFactorAuth')) {
        throw new Exception("TwoFactorAuth class not found. Please check if the package is properly installed.");
    }
    $tfa = new TwoFactorAuth('Library System');
} catch (Exception $e) {
    error_log("2FA Initialization Error: " . $e->getMessage());
    die("Error initializing 2FA: " . $e->getMessage());
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user has initiated 2FA setup
if (!isset($_SESSION['temp_auth_method'])) {
    header("Location: two_factor_settings.php");
    exit();
}

// Debug session data
error_log("Session data: " . print_r($_SESSION, true));

$auth_method = $_SESSION['temp_auth_method'];
$error_message = '';
$success_message = '';

// Generate QR code for authenticator app
if ($auth_method === 'app') {
    try {
        // Debug user ID
        error_log("Session data before user ID check: " . print_r($_SESSION, true));
        
        // Determine the correct user ID based on user type
        $user_id = null;
        if (isset($_SESSION['student_id'])) {
            $user_id = $_SESSION['student_id'];
        } elseif (isset($_SESSION['teacher_id'])) {
            $user_id = $_SESSION['teacher_id'];
        } elseif (isset($_SESSION['librarian_id'])) {
            $user_id = $_SESSION['librarian_id'];
        } elseif (isset($_SESSION['admin_id'])) {
            $user_id = $_SESSION['admin_id'];
        }

        if (!$user_id) {
            throw new Exception("No valid user ID found in session");
        }
        $_SESSION['user_id'] = $user_id; // Set this for consistency

        error_log("Using user ID: " . $user_id . " for 2FA setup");
        
        // Get user's email first
        $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Database prepare error: " . $conn->error);
        }
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!$user) {
            throw new Exception("User not found in database");
        }

        // Generate new secret if not already set
        if (!isset($_SESSION['temp_secret'])) {
            // First check if user already has a secret key
            $check_stmt = $conn->prepare("SELECT secret_key FROM two_factor_auth WHERE user_id = ?");
            $check_stmt->bind_param("i", $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $existing_secret = $check_result->fetch_assoc();

            if ($existing_secret && $existing_secret['secret_key']) {
                // Use existing secret key
                $secret = $existing_secret['secret_key'];
            } else {
                // Generate new secret key
                $secret = $tfa->createSecret();
                
                // Store the new secret key
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
            }
            
            $_SESSION['temp_secret'] = $secret;
        }
        
        // Generate QR code with user's email
        $qrLabel = 'Library System:' . $user['email'];
        $_SESSION['qr_code'] = $tfa->getQRCodeImageAsDataUri($qrLabel, $_SESSION['temp_secret']);
        
        error_log("QR code generated successfully for user: " . $user['email'] . " with secret: " . substr($_SESSION['temp_secret'], 0, 4) . "...");
        
    } catch (Exception $e) {
        $error_message = "Error generating QR code. Please try again.";
        error_log("2FA Error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verification_code = trim($_POST['verification_code']);
    
    if ($auth_method === 'email') {
        // Verify email code
        if (isset($_SESSION['verification_code']) && 
            isset($_SESSION['verification_expiry']) && 
            time() < $_SESSION['verification_expiry']) {
            
            if (strcmp($verification_code, $_SESSION['verification_code']) === 0) {
                // Get the correct user ID
                $user_id = null;
                if (isset($_SESSION['student_id'])) {
                    $user_id = $_SESSION['student_id'];
                } elseif (isset($_SESSION['teacher_id'])) {
                    $user_id = $_SESSION['teacher_id'];
                } elseif (isset($_SESSION['librarian_id'])) {
                    $user_id = $_SESSION['librarian_id'];
                } elseif (isset($_SESSION['admin_id'])) {
                    $user_id = $_SESSION['admin_id'];
                }

                if (!$user_id) {
                    $error_message = "User ID not found. Please try again.";
                    error_log("No valid user ID found during verification");
                    return;
                }

                // Enable 2FA in database
                $stmt = $conn->prepare("UPDATE two_factor_auth SET is_enabled = 1 WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                if ($stmt->execute()) {
                    $success_message = "Two-factor authentication has been enabled successfully!";
                    // Clear temporary session data
                    unset($_SESSION['verification_code']);
                    unset($_SESSION['verification_expiry']);
                    unset($_SESSION['temp_auth_method']);
                    // Redirect after 2 seconds
                    header("refresh:2;url=two_factor_settings.php");
                } else {
                    $error_message = "Failed to enable two-factor authentication.";
                }
            } else {
                $error_message = "Invalid verification code. Please try again.";
            }
        } else {
            $error_message = "Verification code has expired. Please request a new one.";
        }
    } elseif ($auth_method === 'app') {
        // Verify authenticator app code
        if (isset($_SESSION['temp_secret'])) {
            if ($tfa->verifyCode($_SESSION['temp_secret'], $verification_code)) {
                // Get the correct user ID
                $user_id = null;
                if (isset($_SESSION['student_id'])) {
                    $user_id = $_SESSION['student_id'];
                } elseif (isset($_SESSION['teacher_id'])) {
                    $user_id = $_SESSION['teacher_id'];
                } elseif (isset($_SESSION['librarian_id'])) {
                    $user_id = $_SESSION['librarian_id'];
                } elseif (isset($_SESSION['admin_id'])) {
                    $user_id = $_SESSION['admin_id'];
                }

                if (!$user_id) {
                    $error_message = "User ID not found. Please try again.";
                    error_log("No valid user ID found during app verification");
                    return;
                }

                // Enable 2FA in database
                $stmt = $conn->prepare("UPDATE two_factor_auth SET is_enabled = 1 WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                if ($stmt->execute()) {
                    $success_message = "Two-factor authentication has been enabled successfully!";
                    // Clear temporary session data
                    unset($_SESSION['temp_secret']);
                    unset($_SESSION['temp_auth_method']);
                    // Redirect after 2 seconds
                    header("refresh:2;url=two_factor_settings.php");
                } else {
                    $error_message = "Failed to enable two-factor authentication.";
                }
            } else {
                $error_message = "Invalid verification code. Please try again.";
            }
        } else {
            $error_message = "Setup process incomplete. Please start over.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Two-Factor Authentication</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --success: #059669;
            --danger: #dc2626;
            --background: #f8fafc;
            --surface: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
        }

        body {
            background-color: var(--background);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 0;
            color: var(--text);
        }

        .container {
            max-width: 500px;
            padding: 0 1rem;
        }

        .card {
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            border: none;
            background: var(--surface);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border);
            padding: 2rem 2rem 1.5rem;
            text-align: center;
        }

        .card-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text);
            margin: 0;
        }

        .card-body {
            padding: 2rem;
        }

        .auth-icon {
            width: 80px;
            height: 80px;
            background: rgba(37, 99, 235, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            transition: transform 0.3s ease;
        }

        .auth-icon:hover {
            transform: scale(1.1);
        }

        .auth-icon i {
            font-size: 2rem;
            color: var(--primary);
        }

        .form-control {
            padding: 1rem 1.5rem;
            font-size: 1.25rem;
            border-radius: 1rem;
            border: 2px solid var(--border);
            letter-spacing: 0.5em;
            text-align: center;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            padding: 1rem 2rem;
            border-radius: 1rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.2);
        }

        .btn-outline-danger {
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-outline-danger:hover {
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 1rem;
            border: none;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            animation: slideIn 0.3s ease;
        }

        .alert-danger {
            background: rgba(220, 38, 38, 0.1);
            color: var(--danger);
        }

        .alert-success {
            background: rgba(5, 150, 105, 0.1);
            color: var(--success);
        }

        .qr-container {
            background: var(--background);
            border-radius: 1rem;
            padding: 2rem;
            margin: 1.5rem 0;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }

        .qr-code {
            max-width: 200px;
            margin: 1rem auto;
            padding: 1rem;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .manual-code {
            background: white;
            padding: 1rem;
            border-radius: 0.75rem;
            margin: 1rem 0;
            font-family: monospace;
            font-size: 1.1rem;
            color: var(--text);
            display: inline-block;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        #timer {
            font-size: 1rem;
            color: var(--text-light);
            text-align: center;
            margin-top: 1.5rem;
            font-weight: 500;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .instructions {
            color: var(--text-light);
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .step-number {
            display: inline-block;
            width: 24px;
            height: 24px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            font-size: 0.875rem;
            margin-right: 0.5rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Verify Two-Factor Authentication</h2>
            </div>
            <div class="card-body">
                <?php if ($error_message): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $success_message; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <?php if ($auth_method === 'email'): ?>
                            <div class="auth-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <p class="instructions">Please enter the verification code sent to your email.</p>
                        <?php else: ?>
                            <div class="auth-icon">
                                <i class="fas fa-key"></i>
                            </div>
                            <?php if ($auth_method === 'app'): ?>
                                <?php if (isset($_SESSION['qr_code']) && isset($_SESSION['temp_secret'])): ?>
                                    <div class="qr-container">
                                        <h4 class="mb-4">Set Up Authenticator App</h4>
                                        <div class="mb-4">
                                            <p class="mb-3"><span class="step-number">1</span>Scan this QR code with your authenticator app:</p>
                                            <img src="<?php echo htmlspecialchars($_SESSION['qr_code']); ?>" alt="QR Code" class="qr-code">
                                        </div>
                                        <div class="mb-4">
                                            <p class="mb-2"><span class="step-number">2</span>If you can't scan the QR code, enter this code manually:</p>
                                            <code class="manual-code"><?php echo htmlspecialchars($_SESSION['temp_secret']); ?></code>
                                        </div>
                                        <p class="mb-0"><span class="step-number">3</span>Enter the code shown in your authenticator app below:</p>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        Error generating QR code. Please try again.
                                        <br>
                                        <a href="two_factor_settings.php" class="btn btn-outline-danger mt-3">
                                            <i class="fas fa-arrow-left me-2"></i>Go Back
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <form method="POST" class="text-center">
                        <div class="mb-4">
                            <input type="text" name="verification_code" class="form-control" 
                                   placeholder="000000" maxlength="6" pattern="[0-9]{6}" required 
                                   autocomplete="off">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-check me-2"></i>Verify Code
                        </button>
                    </form>

                    <?php if ($auth_method === 'email'): ?>
                        <div id="timer"></div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($auth_method === 'email'): ?>
    <script>
        function updateTimer() {
            const expiryTime = <?php echo $_SESSION['verification_expiry'] ?? 0; ?>;
            const now = Math.floor(Date.now() / 1000);
            const timeLeft = expiryTime - now;
            
            const timerElement = document.getElementById('timer');
            if (timeLeft <= 0) {
                timerElement.innerHTML = '<span class="text-danger"><i class="fas fa-clock me-2"></i>Code has expired. Please request a new code.</span>';
                document.querySelector('button[type="submit"]').disabled = true;
            } else {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                timerElement.innerHTML = `<i class="fas fa-clock me-2"></i>Time remaining: ${minutes}:${seconds.toString().padStart(2, '0')}`;
            }
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    </script>
    <?php endif; ?>
</body>
</html>