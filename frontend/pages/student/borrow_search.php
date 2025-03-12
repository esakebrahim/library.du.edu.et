<?php
include_once '../../../backend-php/database.php';
session_start();
//$user_id = $_SESSION['student_id'];
if (isset($_GET['query'])) {
    $search = "%" . $_GET['query'] . "%";
} else {
    $search = "%";
}

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

$response = "";

if ($availableResult->num_rows > 0) {
    while ($row = $availableResult->fetch_assoc()) {
        $response .= "<div class='book-container'>";
        $response .= "<div class='book-info'>";
        $response .= "<span><strong>Title:</strong> {$row['title']}</span>";
        $response .= "<span><strong>Author:</strong> {$row['author']}</span>";
        $response .= "<span><strong>Status:</strong> {$row['status']}</span>";
        $response .= "</div>";
        $response .= "<form action='../../../backend-php/borrow.php' method='post' onsubmit='return confirmBorrow();'>";
        $response .= "<input type='hidden' name='user_id' value='{$_SESSION['student_id']}'>";
        $response .= "<input type='hidden' name='book_id' value='{$row['id']}'>";
        $response .= "<input type='hidden' name='is_reserved' value='false'>";
        $response .= "<input type='submit' value='Borrow' />";
        $response .= "</form>";
        $response .= "</div>";
    }
} else {
    $response = "<p class='no-books'>No books match your search.</p>";
}

echo $response;
$conn->close();
?>
