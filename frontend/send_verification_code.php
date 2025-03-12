<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
include_once '../backend-php/database.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $redirect_url = 'forgot_password.html';

    // Validate the email
    if (empty($email)) {
        header("Location: " . $redirect_url . "?error=" . urlencode("Please enter your email address"));
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: " . $redirect_url . "?error=" . urlencode("Please enter a valid email address"));
        exit();
    }

    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Generate a 6-digit verification code
        $verification_code = rand(100000, 999999);

        // Save the verification code to the database
        $stmt = $conn->prepare("UPDATE users SET verification_code = ? WHERE email = ?");
        $stmt->bind_param("is", $verification_code, $email);
        
        if (!$stmt->execute()) {
            header("Location: " . $redirect_url . "?error=" . urlencode("Failed to process your request. Please try again."));
            exit();
        }

        // Send the verification code to the user's email
        $mail = new PHPMailer(true);
        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'bekamgbdaw@gmail.com';
            $mail->Password = 'iznb palr txzi wfqe';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            // Email details
            $mail->setFrom('bekamgbdaw@gmail.com', 'Library System');
            $mail->addAddress($email);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Your Password Reset Verification Code';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #6366f1; margin-bottom: 20px;'>Password Reset Verification</h2>
                    <p>Hello,</p>
                    <p>You have requested to reset your password. Here is your verification code:</p>
                    <div style='background: rgba(99, 102, 241, 0.1); padding: 20px; border-radius: 10px; margin: 20px 0; text-align: center;'>
                        <h1 style='color: #6366f1; font-size: 32px; margin: 0;'>{$verification_code}</h1>
                    </div>
                    <p>Please enter this code on the verification page to reset your password.</p>
                    <p>If you didn't request this password reset, please ignore this email.</p>
                    <p style='margin-top: 30px; font-size: 14px; color: #666;'>
                        Best regards,<br>
                        Library System Team
                    </p>
                </div>
            ";

            $mail->send();
            header("Location: reset_password.html?success=" . urlencode("Verification code has been sent to your email. Please check your inbox."));
            exit();
        } catch (Exception $e) {
            header("Location: " . $redirect_url . "?error=" . urlencode("Failed to send verification code. Please try again later."));
            exit();
        }
    } else {
        header("Location: " . $redirect_url . "?error=" . urlencode("No account found with this email address"));
        exit();
    }
} else {
    header("Location: " . $redirect_url . "?error=" . urlencode("Invalid request method"));
    exit();
}

// Close the database connection
$conn->close();
?>
