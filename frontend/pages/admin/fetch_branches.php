<?php
include '../../../backend-php/database.php';
$result = $conn->query("SELECT id, name FROM library_branches");
while ($row = $result->fetch_assoc()) {
    echo "<option value='{$row['id']}'>{$row['name']}</option>";
}
$conn->close();
?>
