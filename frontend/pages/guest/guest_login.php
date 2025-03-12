<?php
session_start();
require_once '../../../backend-php/database.php';

// Generate a random guest ID (unique per session)
if (!isset($_SESSION['guest_id'])) {
    $_SESSION['guest_id'] = "GUEST_" . uniqid();
}

// Redirect to the guest dashboard
header("Location:search_books.php");
exit();
?>
