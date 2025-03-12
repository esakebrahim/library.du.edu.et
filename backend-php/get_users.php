<?php
include_once 'database.php';

$database = new Database();
$conn = $database->getConnection();

$query = "SELECT id, name, type FROM users ORDER BY id DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($users);
?>
