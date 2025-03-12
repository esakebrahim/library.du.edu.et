<?php
include_once 'database.php';
session_start();

if (isset($_GET['id'])) {
    $book_id = (int)$_GET['id'];

    if (!is_numeric($book_id)) {
        echo "Invalid Book ID.";
        exit();
    }

    // Check if the book exists
    $stmt_check = $conn->prepare("SELECT title, branch_id FROM books WHERE id = ?");
    $stmt_check->bind_param("i", $book_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    $stmt_check->bind_result($title, $branch_id);

    if ($stmt_check->num_rows == 1) {
        $stmt_check->fetch();
        $stmt_check->close();

        // Log the action of deleting the book
        $librarian_id = $_SESSION['librarian_id'];
        $action = "Deleted book: $title";
        
        // Prepare SQL to log the deletion action
        $stmt_log = $conn->prepare("INSERT INTO librarian_actions_log (librarian_id, action, book_id, library_branch_id) VALUES (?, ?, ?, ?)");
        $stmt_log->bind_param("isii", $librarian_id, $action, $book_id, $branch_id);
        if (!$stmt_log->execute()) {
            echo "Error logging the action: " . $stmt_log->error;
            exit(); // Stop execution if logging fails
        }
        $stmt_log->close();

        // Now delete related borrow requests
        $stmt_delete_borrow = $conn->prepare("DELETE FROM borrow_requests WHERE book_id = ?");
        $stmt_delete_borrow->bind_param("i", $book_id);
        $stmt_delete_borrow->execute();
        $stmt_delete_borrow->close();

        // Now delete the book
        $stmt_delete_book = $conn->prepare("DELETE FROM books WHERE id = ?");
        $stmt_delete_book->bind_param("i", $book_id);
        if ($stmt_delete_book->execute()) {
            echo "Book '$title' deleted successfully!";
        } else {
            echo "Error deleting book: " . $stmt_delete_book->error;
        }
        $stmt_delete_book->close();
    } else {
        echo "Book not found!";
    }
} else {
    echo "No Book ID provided!";
}

$conn->close();
?>