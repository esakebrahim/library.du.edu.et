<?php
session_start();
include_once '../../../backend-php/database.php';

if (!isset($_SESSION['teacher_id'])) {
    die("Access denied. You must log in as a Teacher.");
}

$user_id = $_SESSION['teacher_id'];

// Get search query
$search = isset($_GET['query']) ? "%" . $_GET['query'] . "%" : "%";

// Prepare and execute query
$availableQuery = "SELECT DISTINCT b.id, b.title, b.author, b.status 
                   FROM books b 
                   LEFT JOIN borrow_requests br ON b.id = br.book_id 
                   WHERE b.status = 'available' 
                   AND (
                       br.book_id IS NULL 
                       OR br.status IS NULL
                       OR br.status = 'return_accept'
                       OR br.status = 'borrow_reject'
                   )
                   AND NOT EXISTS (
                       SELECT 1 FROM borrow_requests br2 
                       WHERE br2.book_id = b.id AND br2.status = 'borrow_pending'
                   )
                   AND (b.title LIKE ? OR b.author LIKE ?)
                   ORDER BY b.title ASC";

$availableStmt = $conn->prepare($availableQuery);
$availableStmt->bind_param("ss", $search, $search);
$availableStmt->execute();
$availableResult = $availableStmt->get_result();

if ($availableResult->num_rows > 0) {
    while ($row = $availableResult->fetch_assoc()) {
        echo "<div class='book-container'>";
        echo "<div class='book-info'>";
        echo "<span><strong>Title:</strong> {$row['title']}</span>";
        echo "<span><strong>Author:</strong> {$row['author']}</span>";
        echo "<span><strong>Status:</strong> {$row['status']}</span>";
        echo "</div>";
        echo "<form action='borrow.php' method='post' onsubmit='return confirmBorrow();'>";
        echo "<input type='hidden' name='user_id' value='{$user_id}'>";
        echo "<input type='hidden' name='book_id' value='{$row['id']}'>";
        echo "<input type='hidden' name='is_reserved' value='false'>";
        echo "<input type='submit' value='Borrow' />";
        echo "</form>";
        echo "</div>";
    }
} else {
    if (strlen(trim($_GET['query'])) > 0) {
        echo "<p class='no-books'>No books found matching your search.</p>";
    } else {
        echo "<p class='no-books'>All available books are reserved.</p>";
    }
}
?> 