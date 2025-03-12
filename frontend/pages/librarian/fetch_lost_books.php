<?php
include '../../../backend-php/database.php';

if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);

    $query = "SELECT b.id, b.title, b.price FROM lost_books lb 
              JOIN books b ON lb.book_id = b.id 
              WHERE lb.user_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $books = [];
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }

    echo json_encode($books);
}
?>
