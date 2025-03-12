<?php
session_start();
include_once 'database.php'; // Ensure this file connects to the database

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    die("Access denied. You must log in to borrow a book.");
}

// Get user input
$user_id = intval($_POST['user_id']);
$book_id = intval($_POST['book_id']);
$is_reserved = $_POST['is_reserved'] === 'true' ? true : false;
$reservation_id = isset($_POST['reservation_id']) ? intval($_POST['reservation_id']) : null;

$conn->begin_transaction(); // Start transaction for atomic updates

try {
    // Check if the user is a student or teacher
    $user_check_sql = "SELECT type FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_check_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_type = $result->fetch_assoc()['type'] ?? '';

    if ($user_type !== 'student' && $user_type !== 'teacher') {
        die("Only students and teachers can borrow books.");
    }

    // Check if the user has already borrowed the maximum number of books
    $count_sql = "SELECT COUNT(*) AS borrow_count FROM borrow_requests WHERE user_id = ? AND status IN ('borrow_pending', 'borrow_accepted')";
    $stmt = $conn->prepare($count_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $borrow_count = $result->fetch_assoc()['borrow_count'] ?? 0;

    if ($borrow_count >= 2) {
        die("You cannot borrow more than 2 books at a time.");
    }

    // Fetch the library branch of the book
    $branchQuery = "SELECT branch_id FROM books WHERE id = ?";
    $branchStmt = $conn->prepare($branchQuery);
    $branchStmt->bind_param("i", $book_id);
    $branchStmt->execute();
    $branchResult = $branchStmt->get_result();
    $branchRow = $branchResult->fetch_assoc();

    if (!$branchRow) {
        die("Error: Book not found.");
    }

    $libraryBranchId = $branchRow['branch_id'];

    // Fetch the librarian assigned to this branch
    $librarianQuery = "SELECT librarian_id FROM librarian_branches WHERE library_branch_id = ?";
    $librarianStmt = $conn->prepare($librarianQuery);
    $librarianStmt->bind_param("i", $libraryBranchId);
    $librarianStmt->execute();
    $librarianResult = $librarianStmt->get_result();
    $librarianRow = $librarianResult->fetch_assoc();

    if ($librarianRow) {
        $librarianId = $librarianRow['librarian_id'];

        // Insert notification for the librarianF
        $notificationMessage = "A book has been requested for borrowing.";
        $insertNotificationQuery = "INSERT INTO notifications (user_id, message, status) VALUES (?, ?, 'unread')";
        $notificationStmt = $conn->prepare($insertNotificationQuery);
        $notificationStmt->bind_param("is", $librarianId, $notificationMessage);
        $notificationStmt->execute();
    }

    // Proceed with borrow request
    if ($is_reserved) {
        $insertBorrowRequest = "INSERT INTO borrow_requests (user_id, book_id, status) VALUES (?, ?, 'borrow_pending')";
        $stmt = $conn->prepare($insertBorrowRequest);
        $stmt->bind_param("ii", $user_id, $book_id);
        $stmt->execute();

        $updateReservationQuery = "UPDATE reservation SET status = 'fulfilled' WHERE reservation_id = ?";
        $stmt = $conn->prepare($updateReservationQuery);
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();
    } else {
        // If the book was not reserved, set status to 'borrow_pending'
        $insertBorrowRequest = "INSERT INTO borrow_requests (user_id, book_id, status) VALUES (?, ?, 'borrow_pending')";
        $stmt = $conn->prepare($insertBorrowRequest);
        $stmt->bind_param("ii", $user_id, $book_id);
        $stmt->execute();
    }

    // Commit transaction
    $conn->commit();

    // Redirect back to the borrow books page with a success message
    header("Location: ../frontend/pages/student/Request borrow.php?success=borrow");
    exit();
} catch (Exception $e) {
    $conn->rollback(); // Rollback changes if any error occurs
    die("Error processing borrow request: " . $e->getMessage());
}

$conn->close();
?>
