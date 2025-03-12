<?php
session_start();
include_once '../../../backend-php/database.php';

if (!isset($_SESSION['teacher_id'])) {
    die("Access denied. Please log in.");
}

$user_id = $_SESSION['teacher_id'];

// Fetch notification count for the badge
$notif_sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? and status='unread'";
$notif_stmt = $conn->prepare($notif_sql);
$notif_stmt->bind_param("i", $user_id);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();
$notif_row = $notif_result->fetch_assoc();
$notification_count = $notif_row['count'];

if (isset($_GET['notification_id'])) {
    $notificationId = intval($_GET['notification_id']);
    
    $sql = "UPDATE notifications SET status = 'read' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $notificationId);
    $stmt->execute();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Feedback</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        .sidebar a.active {
            background: rgba(255, 255, 255, 0.08);
            color: var(--white);
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

        .form-container {
            background: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            transition: var(--transition);
        }

        .form-container:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.75rem;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }

        label {
            display: block;
            font-weight: 500;
            color: var(--secondary);
            margin-bottom: 0.5rem;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: var(--transition);
            margin-bottom: 1.5rem;
            font-family: inherit;
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        textarea {
            height: 150px;
            resize: vertical;
        }

        input[type="submit"] {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: var(--transition);
        }

        input[type="submit"]:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }

        .feedback-container {
            margin-top: 3rem;
        }

        .feedback-entry {
            background: var(--white);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .feedback-entry:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .feedback-entry h3 {
            font-size: 1.25rem;
            color: var(--dark);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .feedback-entry p {
            color: var(--secondary);
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }

        .feedback-entry p strong {
            color: var(--dark);
            font-weight: 500;
        }

        .feedback-entry .response {
            color: var(--success);
            font-weight: 500;
            padding: 0.5rem 1rem;
            background: rgba(34, 197, 94, 0.1);
            border-radius: 0.5rem;
            display: inline-block;
        }

        .feedback-entry .no-response {
            color: var(--danger);
            font-weight: 500;
            padding: 0.5rem 1rem;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 0.5rem;
            display: inline-block;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 500;
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary);
        }

        .date-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 500;
            background: rgba(100, 116, 139, 0.1);
            color: var(--secondary);
        }

        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: var(--primary);
            color: var(--white);
            padding: 0.75rem;
            border-radius: 0.5rem;
            cursor: pointer;
            z-index: 1001;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .mobile-menu-toggle:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        @media (max-width: 1024px) {
            .mobile-menu-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .container {
                margin-left: 0;
                max-width: 100%;
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
        <a href="dashboard.php">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="../../../CRONE/notification.php">
            <i class="fas fa-bell"></i> Notifications
            <?php if ($notification_count > 0): ?>
                <span class="badge"><?php echo $notification_count; ?></span>
            <?php endif; ?>
        </a>
        <a href="feedback_submission.php" class="active">
            <i class="fas fa-comment"></i> Feedback
        </a>
        <a href="search_books.php">
            <i class="fas fa-search"></i> Search Books
        </a>
        <a href="Request borrow.php">
            <i class="fas fa-arrow-right"></i> Borrow Books
        </a>
        <a href="request_return.php">
            <i class="fas fa-arrow-left"></i> Return Books
        </a>
        <a href="view books.php">
            <i class="fas fa-calendar-check"></i> Reservations
        </a>
        <a href="borrowing history.php">
            <i class="fas fa-list"></i> View Borrowed Books
        </a>
        <a href="Request_extension.php">
            <i class="fas fa-clock"></i> Request Extension
        </a>
        <a href="../../login.html">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <div class="container">
        <div class="form-container">
            <h2>Library Feedback Form</h2>
            <form action="../../../backend-php/feedback_submission.php" method="post">
                <label for="student_name">Name (optional):</label>
                <input type="text" id="student_name" name="student_name" placeholder="Enter your name">
                
                <label for="feedback_text">Feedback:</label>
                <textarea id="feedback_text" name="feedback_text" required placeholder="Share your thoughts, suggestions, or concerns..."></textarea>
                
                <input type="submit" value="Submit Feedback">
            </form>
        </div>
        
        <div class="feedback-container">
            <h2>Feedback Entries</h2>
            <?php
            $sql = "SELECT f.id, f.student_id, f.feedback_text, f.created_at, f.status, r.response_text
                    FROM feedback f
                    LEFT JOIN librarian_responses r ON f.id = r.feedback_id
                    ORDER BY f.created_at DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='feedback-entry'>";
                    echo "<h3>" . htmlspecialchars($row['student_id'] ?: 'Anonymous') . "</h3>";
                    echo "<p>" . htmlspecialchars($row['feedback_text']) . "</p>";
                    echo "<p><span class='date-badge'>" . date("M d, Y h:i A", strtotime($row['created_at'])) . "</span></p>";
                    echo "<p><span class='status-badge'>" . htmlspecialchars($row['status']) . "</span></p>";
                    echo "<p>" . ($row['response_text'] ? "<span class='response'>" . htmlspecialchars($row['response_text']) . "</span>" : "<span class='no-response'>Awaiting Response</span>") . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<div class='feedback-entry'><p style='text-align: center; color: var(--secondary);'>No feedback entries available.</p></div>";
            }
            ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.mobile-menu-toggle');
            const sidebar = document.querySelector('.sidebar');

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