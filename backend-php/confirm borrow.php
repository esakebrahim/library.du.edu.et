<?php
session_start();
include_once 'database.php'; // Ensure this file connects to the database

// Check if librarian is logged in
if (!isset($_SESSION['librarian_id'])) {
    die("Access denied. You must log in as a librarian to confirm borrow requests.");
}

// Check if request_id is provided
if (!isset($_POST['request_id'])) {
    die("Invalid request. request_id is missing.");
}

$request_id = intval($_POST['request_id']);

$conn->begin_transaction(); // Start a database transaction

try {
    // Get book_id, book title, and user_id from borrow_requests and books tables
    $getRequestQuery = "
        SELECT br.book_id, br.user_id, b.title 
        FROM borrow_requests br
        JOIN books b ON br.book_id = b.id
        WHERE br.id = ?";
    $stmt = $conn->prepare($getRequestQuery);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();

    if (!$request) {
        throw new Exception("Borrow request not found.");
    }

    $book_id = $request['book_id'];
    $user_id = $request['user_id'];
    $book_title = $request['title']; // Fetching book title

    // Update borrow_request status to 'borrow_accepted' and set due date
    $updateRequestQuery = "UPDATE borrow_requests 
                           SET status = 'borrow_accept', due_date = DATE_ADD(NOW(), INTERVAL 3 DAY) 
                           WHERE id = ?";
    $stmt = $conn->prepare($updateRequestQuery);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();

    // Update book status to 'checked_out'
    $updateBookQuery = "UPDATE books SET status = 'checked_out' WHERE id = ?";
    $stmt = $conn->prepare($updateBookQuery);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();

    // Get user details for notification
    $getUserQuery = "SELECT name, email FROM users WHERE id = ?";
    $stmt = $conn->prepare($getUserQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        throw new Exception("User not found.");
    }

    $student_name = $user['name'];

    // Insert notification for the student
    $notificationMessage = "Dear $student_name, your borrow request for the book '$book_title' has been confirmed. Please collect your book.";
    $insertNotificationQuery = "INSERT INTO notifications (user_id, message, status) VALUES (?, ?, 'unread')";
    $stmt = $conn->prepare($insertNotificationQuery);
    $stmt->bind_param("is", $user_id, $notificationMessage);
    $stmt->execute();

    // Log the action of confirming a borrow request
    $librarian_id = $_SESSION['librarian_id'];
    $action = "Confirmed borrow request for the book '$book_title' by user ID: $user_id";

    $logQuery = "INSERT INTO librarian_actions_log (librarian_id, action, book_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($logQuery);
    $stmt->bind_param("isi", $librarian_id, $action, $book_id);
    $stmt->execute();

    // Commit the transaction
    $conn->commit();

    // Redirect back with success message
    header("Location: pending books.php?success=confirm");
    exit();
} catch (Exception $e) {
    $conn->rollback(); // Rollback changes on error
    die("Error confirming borrow request: " . $e->getMessage());
}

$conn->close();
?>
