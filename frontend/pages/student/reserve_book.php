<?php
session_start(); // Start session

// Check if user is logged in and the book_id is set
if (isset($_SESSION['student_id'], $_POST['id'])) {
    // Connect to database
    $conn = new mysqli("localhost", "root", "", "esak"); // Change connection settings accordingly

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get book ID from the POST request and user ID from the session
    $book_id = intval($_POST['id']);
    $user_id = intval($_SESSION['student_id']); // Assuming user_id is stored in session upon login

    // Check if the user is a student
    $user_check_sql = "SELECT type FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_check_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_type = $result->fetch_assoc()['type'] ?? '';

    if ($user_type !== 'student') {
        echo "Only students can reserve books.";
        exit();
    }

    // Check how many books the student has already reserved
    $count_sql = "SELECT COUNT(*) AS reservation_count FROM reservation WHERE user_id = ? AND status = 'active'";
    $stmt = $conn->prepare($count_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reservation_count = $result->fetch_assoc()['reservation_count'] ?? 0;

    if ($reservation_count >= 2) {
        echo "You cannot reserve more than 2 books at a time.";
        exit();
    }

    // Calculate the expiration date (e.g., 2 days from now)
    $expiration_date = date('Y-m-d H:i:s', strtotime('+2 days'));

    // Check if the book is available
    $check_sql = "SELECT status FROM books WHERE id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['status'] == 'available') {
            // Begin transaction
            $conn->begin_transaction();
            try {
                // Insert reservation
                $reserve_sql = "INSERT INTO reservation (user_id, book_id, reservation_date, expiration_date, status) 
                                VALUES (?, ?, NOW(), ?, 'active')";
                $stmt = $conn->prepare($reserve_sql);
                $stmt->bind_param("iis", $user_id, $book_id, $expiration_date);
                $stmt->execute();

                // Update book status to 'reserved'
                $update_sql = "UPDATE books SET status = 'reserved' WHERE id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("i", $book_id);
                $stmt->execute();

                // Commit transaction
                $conn->commit();
                echo "Book reserved successfully. Please check out by " . $expiration_date;
            } catch (Exception $e) {
                // Rollback the transaction if something failed
                $conn->rollback();
                echo "Error reserving book: " . $e->getMessage();
            }
        } else {
            echo "Book is not available for reservation. Current status: " . $row['status'];
        }
    } else {
        echo "Book not found.";
    }

    // Close connection
    $conn->close();
} else {
    echo "User not logged in or no book ID provided.";
}
?>
