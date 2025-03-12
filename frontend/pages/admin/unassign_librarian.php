<?php
include '../../../backend-php/database.php';

$librarian_id = $_POST['librarian_id'];

$stmt = $conn->prepare("DELETE FROM librarian_branches WHERE librarian_id = ?");
$stmt->bind_param("i", $librarian_id);

if ($stmt->execute()) {
    echo "<div class='alert alert-success'>Librarian unassigned successfully!</div>";
} else {
    echo "<div class='alert alert-danger'>Error unassigning librarian.</div>";
}

$conn->close();
?>
