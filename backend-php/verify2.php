<?php
session_start();
include_once 'database.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

header('Content-Type: application/json');

if (isset($_GET['email'])) {
    $email = $_GET['email'];
    
    // Store email in session
    $_SESSION['pending_email'] = $email;

    // Get user details from database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        // Generate a new verification code
        $verificationCode = rand(100000, 999999);

        // Update code in database
        $updateStmt = $conn->prepare("UPDATE users SET verification_code = ? WHERE email = ?");
        $updateStmt->bind_param("is", $verificationCode, $email);
        
        if ($updateStmt->execute()) {
            // Send email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'bekamgbdaw@gmail.com';
                $mail->Password = 'iznb palr txzi wfqe';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                $mail->setFrom('bekamgbdaw@gmail.com', 'Library System');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Your Verification Code';
                $mail->Body = "<p>Hi {$row['name']},</p>
                              <p>Your verification code is: <strong>$verificationCode</strong></p>
                              <p>Please enter this code on the verification page to activate your account.</p>";

                $mail->send();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Verification code sent successfully!',
                    'email' => $email,
                    'name' => $row['name']
                ]);
                exit();
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to send verification email. Please try again later.'
                ]);
                exit();
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update verification code. Please try again.'
            ]);
            exit();
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'User not found.'
        ]);
        exit();
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request.'
    ]);
    exit();
}
?>
