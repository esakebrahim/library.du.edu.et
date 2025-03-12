<?php
session_start();
include_once '../backend-php/database.php';

// Check if user is logged in
if (!isset($_SESSION['teacher_id'])) {
    die("Access denied. Please log in.");
}

$user_id = $_SESSION['teacher_id'];

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

// Fetch notification count for the badge
$notif_sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? and status='unread'";
$notif_stmt = $conn->prepare($notif_sql);
$notif_stmt->bind_param("i", $user_id);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();
$notif_row = $notif_result->fetch_assoc();
$notification_count = $notif_row['count'];

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Library System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #64748b;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --light: #f8fafc;
            --dark: #1e293b;
            --white: #ffffff;
            --sidebar-width: 280px;
            --header-height: 70px;
            --transition: all 0.3s ease;
        }

        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light);
            color: var(--dark);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(to bottom, var(--dark), #2d3748);
            color: var(--white);
            padding: 1.5rem;
            transition: var(--transition);
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 1rem 0 2rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1rem;
        }

        .sidebar-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--white);
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            transition: var(--transition);
            font-weight: 500;
        }

        .sidebar a.active {
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            transform: translateX(5px);
        }

        .sidebar a i {
            width: 1.5rem;
            margin-right: 1rem;
            font-size: 1.1rem;
        }

        .badge {
            background: var(--danger);
            color: var(--white);
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            font-size: 0.75rem;
            margin-left: auto;
        }

        .container {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
            max-width: calc(100% - var(--sidebar-width));
        }

        .page-header {
            background: var(--white);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            text-align: center;
            background-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--white);
        }

        .page-header h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .notifications-container {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .notification {
            background: var(--light);
            padding: 1.25rem;
            margin-bottom: 1rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            transition: var(--transition);
        }

        .notification:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .notification:last-child {
            margin-bottom: 0;
        }

        .notification .icon {
            color: var(--primary);
            font-size: 1.25rem;
            padding: 0.5rem;
            background: rgba(79, 70, 229, 0.1);
            border-radius: 0.5rem;
        }

        .notification-content {
            flex: 1;
        }

        .notification p {
            color: var(--dark);
            font-size: 0.95rem;
            line-height: 1.5;
            margin: 0;
        }

        .time {
            color: var(--secondary);
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }

        .no-notifications {
            text-align: center;
            padding: 3rem 1.5rem;
            color: var(--secondary);
            font-size: 1.1rem;
        }

        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: var(--primary);
            color: var(--white);
            padding: 0.5rem;
            border-radius: 0.5rem;
            cursor: pointer;
            z-index: 1001;
        }

        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .container {
                margin-left: 0;
                max-width: 100%;
            }

            .mobile-menu-toggle {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .page-header {
                padding: 1.5rem;
            }

            .page-header h2 {
                font-size: 1.5rem;
            }

            .notification {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="mobile-menu-toggle">
        <i class="fas fa-bars"></i>
    </div>

    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Library System</h2>
        </div>
        <a href="../frontend/pages/teacher/dashboard.php">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="../CRONE/notification.php" class="active">
            <i class="fas fa-bell"></i> Notifications
            <?php if ($notification_count > 0): ?>
                <span class="badge"><?php echo $notification_count; ?></span>
            <?php endif; ?>
        </a>
        <a href="../frontend/pages/teacher/feedback_submission.php">
            <i class="fas fa-comment"></i> Feedback
        </a>
        <a href="../frontend/pages/teacher/search_books.php">
            <i class="fas fa-search"></i> Search Books
        </a>
        <a href="../frontend/pages/teacher/Request borrow.php">
            <i class="fas fa-arrow-right"></i> Borrow Books
        </a>
        <a href="../frontend/pages/teacher/request_return.php">
            <i class="fas fa-arrow-left"></i> Return Books
        </a>
        <a href="../frontend/pages/teacher/view books.php">
            <i class="fas fa-calendar-check"></i> Reservations
        </a>
        <a href="../frontend/pages/teacher/borrowing history.php">
            <i class="fas fa-list"></i> View Borrowed Books
        </a>
        <a href="../frontend/pages/teacher/Request_extension.php">
            <i class="fas fa-clock"></i> Request Extension
        </a>
        <a href="../frontend/login.html">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <div class="container">
        <div class="page-header">
            <h2>Notifications</h2>
            <p>Stay updated with your library activities</p>
        </div>

        <div class="notifications-container">
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification">
                        <div class="icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="notification-content">
                            <p><?php echo htmlspecialchars($notification['message']); ?></p>
                            <div class="time"><?php echo date("M d, Y h:i A", strtotime($notification['created_at'])); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-notifications">
                    <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--success); margin-bottom: 1rem;"></i>
                    <p>You're all caught up! No new notifications.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.mobile-menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            const container = document.querySelector('.container');

            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 1024) {
                    if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                        sidebar.classList.remove('active');
                    }
                }
            });
        });
    </script>
</body>
</html>
