<?php
session_start();
$conn = new mysqli("localhost", "root", "", "esak");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $borrow_id = $_POST['borrow_id'];
    $book_id = $_POST['book_id'];
    $teacher_id = $_SESSION['teacher_id'];

    // Verify the borrow request is valid for extension
    $check_sql = "SELECT * FROM borrow_requests 
                  WHERE id = $borrow_id 
                  AND user_id = $teacher_id 
                  AND status = 'borrow_accept'
                  AND due_date < CURDATE() 
                  AND DATE_ADD(due_date, INTERVAL 1 DAY) >= CURDATE()";
                  
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows == 0) {
        echo "<script>alert('Invalid request. The due date has either not expired or more than 1 day has passed.'); window.location.href='request_extension.php';</script>";
        exit();
    }

    // Check if an extension request already exists
    $check_ext_sql = "SELECT * FROM extension_requests WHERE borrow_id = $borrow_id AND user_id = $teacher_id";
    $check_ext_result = $conn->query($check_ext_sql);

    if ($check_ext_result->num_rows > 0) {
        echo "<script>alert('You have already requested an extension for this book.'); window.location.href='request_extension.php';</script>";
        exit();
    }

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
        $notificationMessage = "extenstion request submitted successfully!";
        $insertNotificationQuery = "INSERT INTO notifications (user_id, message, status) VALUES (?, ?, 'unread')";
        $notificationStmt = $conn->prepare($insertNotificationQuery);
        $notificationStmt->bind_param("is", $librarianId, $notificationMessage);
        $notificationStmt->execute();
    }

    // Insert extension request
    $sql = "INSERT INTO extension_requests (borrow_id, user_id, status, request_date) 
            VALUES ($borrow_id, $teacher_id, 'pending', NOW())";

    if ($conn->query($sql) === TRUE) {


        echo "<script>alert('Extension request submitted successfully!'); window.location.href='request_extension.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
