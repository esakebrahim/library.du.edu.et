<?php
include_once '../../../backend-php/database.php'; 

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = isset($_GET['query']) ? "%" . $_GET['query'] . "%" : "%";

$sql = "SELECT books.id, books.title, books.author FROM books
        LEFT JOIN borrow_requests ON books.id = borrow_requests.book_id 
        WHERE books.status='available' 
        AND (borrow_requests.book_id IS NULL 
        OR borrow_requests.status='borrow_reject' 
        OR borrow_requests.status='return_accept')
        AND (books.title LIKE ? OR books.author LIKE ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $query, $query);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['title']}</td>
                <td>{$row['author']}</td>
                <td>
                    <form method='POST' action='reserve_book.php'>
                        <input type='hidden' name='id' value='{$row['id']}'>
                        <input type='submit' class='action-btn' value='Reserve' />
                    </form>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='4'>No books found</td></tr>";
}

$stmt->close();
$conn->close();
?>
