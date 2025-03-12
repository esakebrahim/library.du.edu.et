<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Include the database connection
include_once 'database.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = ['success' => false, 'message' => ''];
    
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm-password"]);
    $type = trim($_POST["type"]);

    // Check if fields are not empty
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($type)) {
        $response['message'] = 'All fields are required!';
        echo json_encode($response);
        exit();
    }

    // Validate password length
    if (strlen($password) < 6) {
        $response['message'] = 'Password must be at least 6 characters long';
        echo json_encode($response);
        exit();
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        $response['message'] = 'Passwords do not match!';
        echo json_encode($response);
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $response['message'] = 'Email is already taken! Please choose another email.';
        echo json_encode($response);
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Generate a 6-digit verification code
    $verification_code = rand(100000, 999999);

    // Insert user into the database with verification code
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, type, verification_code, is_confirmed) VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("ssssi", $name, $email, $hashed_password, $type, $verification_code);

    if ($stmt->execute()) {
        // Send verification email
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
            $mail->Subject = 'Your Verification Code';
            $mail->Body    = "<p>Hi $name,</p>
                              <p>Your verification code is: <strong>$verification_code</strong></p>
                              <p>Please enter this code on the verification page to activate your account.</p>";

            $mail->send();
            $_SESSION['pending_email'] = $email;
            $response['success'] = true;
            $response['redirect'] = '../backend-php/verify.php';
            echo json_encode($response);
            exit();
        } catch (Exception $e) {
            $response['message'] = 'Failed to send verification email. Please try again later.';
            echo json_encode($response);
            exit();
        }
    } else {
        $response['message'] = 'Error during registration. Please try again.';
        echo json_encode($response);
        exit();
    }
}

// Close the database connection
$conn->close();
?>
