<?php
session_start();
$conn = new mysqli("localhost", "root", "", "esak");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $extension_id = $_POST['extension_id'];
    $decision = $_POST['decision'];

    // Validate extension request
    $check_sql = "SELECT * FROM extension_requests WHERE id = $extension_id AND status = 'pending'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows == 0) {
        echo "<script>alert('Invalid request.'); window.location.href='manage_extensions.php';</script>";
        exit();
    }

    if ($decision === 'approved') {
       
        $update_borrow_sql = "UPDATE borrow_requests 
                              SET due_date = DATE_ADD(due_date, INTERVAL 3 DAY) 
                              WHERE id = (SELECT borrow_id FROM extension_requests WHERE id = $extension_id)";
        $conn->query($update_borrow_sql);

        // Mark the extension request as approved
        $update_ext_sql = "UPDATE extension_requests SET status = 'approved' WHERE id = $extension_id";
        $conn->query($update_ext_sql);

        echo "<script>alert('Extension request approved successfully!'); window.location.href='manage_extensions.php';</script>";
    } elseif ($decision === 'rejected') {
        // Mark the extension request as rejected
        $update_ext_sql = "UPDATE extension_requests SET status = 'rejected' WHERE id = $extension_id";
        $conn->query($update_ext_sql);

        echo "<script>alert('Extension request rejected.'); window.location.href='manage_extensions.php';</script>";
    }
}

$conn->close();
?>
