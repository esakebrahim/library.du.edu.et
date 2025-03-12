<?php
session_start();
include_once '../../../backend-php/database.php'; // Ensure this file connects to the database
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../../vendor/autoload.php'; // Include PHPMailer

// Check if user is logged in
if (!isset($_SESSION['student_id']) || !isset($_SESSION['student_name'])) {
    header("Location: ../../login.html");
    exit();
}

// Retrieve user details from the session
$student_id = $_SESSION['student_id'];
$studentName = $_SESSION['student_name'];

// Get unread notifications count
$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND status = 'unread' and type='general'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications_count = $result->fetch_assoc()['unread_count'];
$stmt->close();

// Get unread feedback count
$sql = "SELECT COUNT(*) AS feedback_count FROM notifications WHERE user_id = ? AND status = 'unread' AND type = 'feedback'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$feedbackCount = $row['feedback_count'] ?? 0;
$stmt->close();

// Get latest unread notification ID
$sql = "SELECT id FROM notifications WHERE user_id = ? AND status = 'unread' AND type = 'feedback' LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$notificationId = $row['id'] ?? null;
$stmt->close();

// Fetch the current email from the database
$emailQuery = "SELECT email FROM users WHERE id = ?";
$emailStmt = $conn->prepare($emailQuery);
$emailStmt->bind_param("i", $student_id);
$emailStmt->execute();
$emailResult = $emailStmt->get_result();
$userData = $emailResult->fetch_assoc();
$currentEmail = $userData['email'] ?? '';

// Handle form submission
$successMsg = $errorMsg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newEmail = trim($_POST['email']);
    $newPassword = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    
    // Validate email
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Invalid email format.";
    } elseif (!empty($newPassword)) {
        // Validate password and get specific error messages
        $passwordErrors = validatePassword($newPassword);
        if (!empty($passwordErrors)) {
            $errorMsg = implode("<br>", $passwordErrors);
        } elseif ($newPassword !== $confirmPassword) {
            $errorMsg = "Passwords do not match.";
        } else {
            // Update password in the database
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $updateQuery = "UPDATE users SET password = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("si", $hashedPassword, $student_id);
            if ($updateStmt->execute()) {
                $successMsg = "Password updated successfully!";
            } else {
                $errorMsg = "Failed to update password.";
            }
        }
    }

    // Check if email has changed
    if ($newEmail !== $currentEmail) {
        $verificationCode = rand(100000, 999999);
        $_SESSION['email_change'] = $newEmail;
        $_SESSION['verification_code'] = $verificationCode;

        sendVerificationEmail($newEmail, $verificationCode);
        $successMsg = "Profile updated successfully! A verification email has been sent.";
        header("Location: verify_email.php");
        exit();
    }
}

// Function to validate password and return errors
function validatePassword($password) {
    $errors = [];

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must have at least one uppercase letter.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must have at least one lowercase letter.";
    }
    if (!preg_match('/\d/', $password)) {
        $errors[] = "Password must have at least one number.";
    }
    if (!preg_match('/[@$!%*?&]/', $password)) {
        $errors[] = "Password must have at least one special character (@, $, !, %, *, ?, &).";
    }
    if (preg_match('/(ab|bc|cd|de|ef|fg|gh|hi|ij|jk|kl|lm|mn|no|op|pq|qr|rs|st|tu|uv|vw|wx|xy|yz|za)/i', $password)) {
        $errors[] = "Password must not contain consecutive letters (e.g., 'ab', 'yz').";
    }
    if (preg_match('/(01|12|23|34|45|56|67|78|89|90)/', $password)) {
        $errors[] = "Password must not contain consecutive numbers (e.g., '12', '89').";
    }

    return $errors;
}

// Function to send the verification email
function sendVerificationEmail($email, $verificationCode) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com'; // Change to your email
        $mail->Password = 'your-app-password'; // Use an app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Email details
        $mail->setFrom('your-email@gmail.com', 'Library System');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification Code';
        $mail->Body = "<p>Hi,</p>
                      <p>Your verification code is: <strong>$verificationCode</strong></p>
                      <p>Please enter this code to confirm your email address.</p>";

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #343a40;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            background: linear-gradient(135deg, #4361ee, #3f37c9);
            padding-top: 1.5rem;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar h4 {
            text-align: center;
            font-weight: 700;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar a {
            color: rgba(255, 255, 255, 0.9);
            padding: 0.8rem 1.5rem;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 0.2rem 0.8rem;
            border-radius: 10px;
        }

        .sidebar a i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }

        .notification-badge {
            background-color: #f72585;
            color: white;
            border-radius: 20px;
            padding: 0.25rem 0.6rem;
            font-size: 0.75rem;
            margin-left: auto;
            font-weight: 600;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        header {
            width: 100%;
            background: #007bff;
            color: #ffffff;
            padding: 15px;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            position: relative;
            margin-bottom: 20px;
            border-radius: 10px;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .form-group label {
            font-weight: 600;
            color: #2d3748;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 10px 15px;
        }

        .form-control:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }

        .btn-custom {
            background: #4361ee;
            color: white;
            border-radius: 8px;
            padding: 12px 20px;
            font-weight: 600;
            width: 100%;
            margin-bottom: 10px;
        }

        .btn-custom:hover {
            background: #3f37c9;
            color: white;
        }

        .btn-secondary {
            border-radius: 8px;
            padding: 12px 20px;
            font-weight: 600;
            width: 100%;
        }

        /* Mobile Toggle Button */
        .toggle-sidebar {
            display: none;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .toggle-sidebar {
                display: block;
                position: fixed;
                top: 1rem;
                left: 1rem;
                z-index: 1001;
                background: #4361ee;
                color: white;
                padding: 0.5rem;
                border-radius: 5px;
                cursor: pointer;
            }

            .container {
                margin: 10px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Toggle Button -->
    <div class="toggle-sidebar d-lg-none">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <h4><i class="fas fa-book-reader"></i> Student Portal</h4>
        <a href="First Page.php"><i class="fas fa-home"></i><span>Dashboard</span></a>
        <a href="view books.php"><i class="fas fa-book"></i> View Available Books</a>
        <a href="Request borrow.php"><i class="fas fa-hand-holding"></i> Borrow a Book</a>
        <a href="request_return.php"><i class="fas fa-undo"></i> Request Return</a>
        <a href="borrowing history.php"><i class="fas fa-history"></i> My Borrowing History</a>
        <a href="search_books.php"><i class="fas fa-search"></i> Search for Books</a>
        <a href="Display Fine.php"><i class="fas fa-wallet"></i> Payment</a>
        <a href="report_lost.php"><i class="fas fa-exclamation-triangle"></i> Report Lost Book</a>
        <a class="nav-link active" href="#"><i class="fas fa-user-cog"></i> Profile Settings</a>
        <a href="notifications.php">
            <i class="fas fa-bell"></i> Notifications
            <?php if (isset($notifications_count) && $notifications_count > 0): ?>
                <span class="notification-badge"><?php echo $notifications_count; ?></span>
            <?php endif; ?>
        </a>
        <a href="feedback_submission.php?notification_id=<?php echo $notificationId ?? ''; ?>">
            <i class="fas fa-comment-alt"></i> Feedback
            <?php if (isset($feedbackCount) && $feedbackCount > 0): ?>
                <span class="notification-badge"><?php echo $feedbackCount; ?></span>
            <?php endif; ?>
        </a>
        <a href="../../login.html"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header>Profile Settings</header>

        <div class="container">
            <?php if ($successMsg): ?>
                <div class="alert alert-success"><?php echo $successMsg; ?></div>
            <?php elseif ($errorMsg): ?>
                <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <!-- Name (Read-Only) -->
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($studentName); ?>" readonly>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label>New Email</label>
                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($currentEmail); ?>" required>
                </div>

                <!-- New Password -->
                <div class="form-group">
                    <label>New Password (Optional)</label>
                    <input type="password" class="form-control" name="password" placeholder="Enter a strong password">
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" class="form-control" name="confirm_password">
                </div>

                <button type="submit" class="btn btn-custom">Update Profile</button>
                <a href="First Page.php" class="btn btn-secondary">Back to Dashboard</a>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        $(document).ready(function() {
            $('.toggle-sidebar').click(function() {
                $('.sidebar').toggleClass('active');
            });

            // Close sidebar when clicking outside on mobile
            $(document).click(function(event) {
                if (!$(event.target).closest('.sidebar, .toggle-sidebar').length) {
                    $('.sidebar').removeClass('active');
                }
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
