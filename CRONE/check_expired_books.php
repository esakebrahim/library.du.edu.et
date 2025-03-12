<?php
include_once '../backend-php/database.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';

// 1️⃣ Find expired reservations
$sql_reservation = "SELECT r.reservation_id, r.user_id, r.book_id, b.title 
                    FROM reservation r
                    JOIN books b ON r.book_id = b.id
                    WHERE r.expiration_date < NOW() AND r.status = 'active'
                    AND b.status !='lost'";

$result = $conn->query($sql_reservation);
while ($row = $result->fetch_assoc()) {
    $user_id = $row['user_id'];
    $book_id = $row['book_id'];
    $book_title = $row['title'];

    // Add in-app notification using a prepared statement
    $message = "Your reservation for '$book_title' has expired.";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
    $stmt->close(); // Close the statement

    // Send email notification
    sendEmailNotification($user_id, $message);

    // Update reservation status & book availability
    $conn->query("UPDATE reservation SET status = 'expired' WHERE reservation_id = " . $row['reservation_id']);
    $conn->query("UPDATE books SET status = 'available' WHERE id = $book_id");
}

// 2️⃣ Find overdue borrowed books
$sql_borrow = "SELECT br.id, br.user_id, br.book_id, b.title 
               FROM borrow_requests br
               JOIN books b ON br.book_id = b.id
               WHERE br.due_date < NOW() AND br.status = 'borrow_accept'";

$result = $conn->query($sql_borrow);
while ($row = $result->fetch_assoc()) {
    $user_id = $row['user_id'];
    $book_title = $row['title'];

    // Add in-app notification using a prepared statement
    $message = "Your borrowed book '$book_title' is overdue!";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
    $stmt->close(); // Close the statement

    // Send email notification
    sendEmailNotification($user_id, $message);
}

function sendEmailNotification($user_id, $message) {
    global $conn; // Use the database connection

    // Fetch user's email from database
    $sql = "SELECT email FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $to_email = $row['email'];
    } else {
        return false; 
    }

    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'bekamgbdaw@gmail.com'; 
        $mail->Password = 'iznb palr txzi wfqe'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Email details
        $mail->setFrom('bekamgbdaw@gmail.com', 'Library System');
        $mail->addAddress($to_email); // Send to user
        $mail->Subject = "Library Notification"; 
        $mail->Body = $message;

        // Send Email
        if ($mail->send()) {
            return true; // Email sent successfully
        } else {
            return false; // Email failed
        }
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo); // Log error for debugging
        return false;
    }
}

$conn->close();
?>