<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../../vendor/autoload.php'; // Ensure this path is correct
include_once '../../../backend-php/database.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    die("Unauthorized access.");
}

$adminId = $_SESSION['admin_id'];

// Check if a file was uploaded
if (isset($_FILES['reportFile']) && $_FILES['reportFile']['error'] == 0) {
    $uploadDir = __DIR__ . '/../../../reports/'; // Absolute path for storage
    $fileName = basename($_FILES['reportFile']['name']);

    // Validate file type (Allow only PDFs)
    $allowedTypes = ['application/pdf'];
    if (!in_array($_FILES['reportFile']['type'], $allowedTypes)) {
        die("Only PDF files are allowed.");
    }

    // Ensure unique file names to avoid overwriting
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $uniqueFileName = time() . '_' . uniqid() . '.' . $fileExtension;
    $filePath = $uploadDir . $uniqueFileName;

    // Move the uploaded file
    if (move_uploaded_file($_FILES['reportFile']['tmp_name'], $filePath)) {
        // Fetch all librarians' emails
        $librarians = $conn->query("SELECT id,email FROM users WHERE type = 'librarian'");

        if ($librarians->num_rows > 0) {
            while ($row = $librarians->fetch_assoc()) {
                $librarianEmail = $row['email'];
                $user_id = $row['id'];

                // Send email with attachment
                $mail = new PHPMailer(true);
                try {
                    // Email Configuration
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'bekamgbdaw@gmail.com'; // Change to your email
                    $mail->Password = 'iznb palr txzi wfqe'; // Use an app password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = 465;

                    // Sender & Recipient
                    $mail->setFrom('bekamgbdaw@gmail.com', 'Library Admin');
                    $mail->addAddress($librarianEmail);

                    // Email Content
                    $mail->isHTML(true);
                    $mail->Subject = 'New Library Report Available';
                    $mail->Body = 'Dear Librarian,<br><br>A new library report is available. Please find the attached file.<br><br>Best Regards,<br>Library Admin';
                    $mail->addAttachment($filePath, $fileName); // Attach file

                    // Send Email
                    $mail->send();


                    $message = "A new library report has been sent to your email.";
                    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                    $stmt->bind_param("is", $user_id, $message);
                    $stmt->execute();
                    $stmt->close();
                } catch (Exception $e) {
                    echo "Email could not be sent to $librarianEmail. Error: {$mail->ErrorInfo}<br>";
                }
            }
       
            echo "Report successfully sent to librarians via email.";


        } else {
            echo "No librarians found.";
        }
    } else {
        echo "Failed to upload file.";
    }
} else {
    echo "No file uploaded or file upload error.";
}

// Close connection
$conn->close();
?>
