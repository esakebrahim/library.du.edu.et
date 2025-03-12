<?php
include_once 'database.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get current time
$current_time = date('Y-m-d H:i:s');

// SQL query to update the status of expired reservations in `reserved_books` table to 'expired' 
// and update related books status to 'available' in the `books` table
$sql = "
    UPDATE reservation rb
    JOIN books b ON rb.book_id = b.id
    SET rb.status = 'expired', b.status = 'available'
    WHERE rb.status = 'reserved' 
    AND rb.expiration_date < '$current_time' 
";

// Execute the query
if ($conn->query($sql) === TRUE) {
    // Check how many rows were affected
    if ($conn->affected_rows > 0) {
        echo "Expired reservations updated to 'expired' and books set to 'available'.";
    } else {
        echo "No expired reservations found.";
    }
} else {
    echo "Error updating records: " . $conn->error;
}

$conn->close();
?>
