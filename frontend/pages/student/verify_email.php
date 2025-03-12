<?php
session_start();
include_once '../../../backend-php/database.php'; // Ensure this file connects to the database

// Check if the user is logged in and the email change process is pending
if (!isset($_SESSION['id']) || !isset($_SESSION['email_change'])) {
    header("Location: ../../login.html");
    exit();
}

$user_id = $_SESSION['student_id'];
$newEmail = $_SESSION['email_change'];  // The new email address is stored in session temporarily

$verificationMsg = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $verificationCode = trim($_POST['verification_code']);
    
    // Fetch the stored verification code from the session
    $storedVerificationCode = $_SESSION['verification_code'];

    if ($verificationCode == $storedVerificationCode) {
        // Update the email in the database
        $updateQuery = "UPDATE users SET email = ?, is_confirmed = 1 WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("si", $newEmail, $user_id);

        if ($stmt->execute()) {
            // Successfully updated the email
            unset($_SESSION['email_change'], $_SESSION['verification_code']);  // Clear session variables
            $verificationMsg = "Your email has been successfully updated!";
        } else {
            $verificationMsg = "Error updating email in the database.";
        }
    } else {
        $verificationMsg = "Incorrect verification code.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: #f4f4f9;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            max-width: 500px;
            margin-top: 50px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 16px;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="../../login.html" class="btn btn-secondary back-btn">Back to Login</a>

    <h3 class="text-center">Email Verification</h3>

    <?php if ($verificationMsg): ?>
        <div class="alert alert-info"><?php echo $verificationMsg; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Enter the verification code sent to your new email</label>
            <input type="text" class="form-control" name="verification_code" required>
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">Verify Email</button>
    </form>
</div>

</body>
</html>

<?php
$conn->close();
?>
