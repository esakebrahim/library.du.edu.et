<?php
session_start();
include_once '../backend-php/database.php';

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    die("Access denied. Please log in.");
}

$user_id = $_SESSION['student_id'];

// Fetch unread notifications
$sql = "SELECT id, message, created_at FROM notifications WHERE user_id = ? AND status = 'unread' ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

// **Mark notifications as read**
if (!empty($notifications)) {
    $update_sql = "UPDATE notifications SET status = 'read' WHERE user_id = ? AND status = 'unread'";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $user_id);
    $update_stmt->execute();
    $update_stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f9f9f9;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        .header {
            width: 100%;
            max-width: 600px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            padding: 15px;
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .back-btn {
            display: flex;
            align-items: center;
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            font-size: 14px;
            border-radius: 20px;
            cursor: pointer;
            transition: background 0.3s;
            text-decoration: none;
        }

        .back-btn i {
            margin-right: 8px;
        }

        .back-btn:hover {
            background: #0056b3;
        }

        .container {
            width: 90%;
            max-width: 600px;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-top: 70px; /* Push down to avoid overlapping with fixed header */
        }

        h2 {
            margin-bottom: 15px;
            color: #333;
        }

        .notification {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            box-shadow: 0px 3px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            transition: transform 0.2s ease-in-out;
        }

        .notification:hover {
            transform: scale(1.02);
        }

        .notification p {
            margin: 0;
            color: #333;
            font-size: 15px;
            flex: 1;
        }

        .time {
            font-size: 12px;
            color: #888;
            text-align: right;
        }

        .icon {
            color: #007bff;
            font-size: 18px;
            margin-right: 10px;
        }

        .no-notifications {
            color: #777;
            font-size: 16px;
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="header">
    <a href="../frontend/pages/student/first page.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="container">
    <h2>Your Notifications</h2>

    <?php if (!empty($notifications)): ?>
        <?php foreach ($notifications as $notification): ?>
            <div class="notification">
                <i class="fas fa-bell icon"></i>
                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                <p class="time"><?php echo date("M d, Y h:i A", strtotime($notification['created_at'])); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-notifications">No new notifications.</p>
    <?php endif; ?>
</div>

</body>
</html>
