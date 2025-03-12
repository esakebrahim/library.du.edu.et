<?php
session_start();
include_once '../../../backend-php/database.php'; // Include your database connection

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    die("Access denied. You must log in to view this page.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bookId = $_POST['book_id'];
    $userId = $_SESSION['student_id'];

    // Update the book status to ‘pending return’ and update borrow request status
    $updateQuery = "UPDATE borrow_requests SET return_status = 'pending' where book_id = ? and user_id = ?";          
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ii", $bookId, $userId);

    if ($stmt->execute()) {
        echo "Return request submitted successfully.";
    } else {
        echo "Error submitting return request: " . $stmt->error;
    }

    $stmt->close();
}
?>

<a href="sidebar.php">Back to Menu</a>

<?php
$conn->close();
?>
