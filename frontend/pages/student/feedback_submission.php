<?php
session_start();
include_once '../../../backend-php/database.php';

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: ../../login.html");
    exit();
}

$student_id = $_SESSION['student_id'];
$studentName = $_SESSION['student_name'];

// Get unread notifications count
$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND status = 'unread' and type='general'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications_count = $result->fetch_assoc()['unread_count'];
$stmt->close();

// Get unread feedback count
$sql = "SELECT COUNT(*) AS feedback_count FROM notifications WHERE user_id = ? AND status = 'unread' AND type = 'feedback'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$feedbackCount = $row['feedback_count'] ?? 0;
$stmt->close();

// Get latest unread notification ID
$sql = "SELECT id FROM notifications WHERE user_id = ? AND status = 'unread' AND type = 'feedback' LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$notificationId = $row['id'] ?? null;
$stmt->close();

// Mark notification as read if notification_id is provided
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #343a40;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            background: linear-gradient(135deg, #4361ee, #3f37c9);
            padding-top: 1.5rem;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar h4 {
            text-align: center;
            font-weight: 700;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar a {
            color: rgba(255, 255, 255, 0.9);
            padding: 0.8rem 1.5rem;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 0.2rem 0.8rem;
            border-radius: 10px;
        }

        .sidebar a i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }

        .notification-badge {
            background-color: #f72585;
            color: white;
            border-radius: 20px;
            padding: 0.25rem 0.6rem;
            font-size: 0.75rem;
            margin-left: auto;
            font-weight: 600;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        header {
            width: 100%;
            background: #007bff;
            color: #ffffff;
            padding: 15px;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            position: relative;
            margin-bottom: 20px;
            border-radius: 10px;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .form-group label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 12px;
            margin-bottom: 20px;
        }

        .form-control:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }

        .btn-custom {
            background: #4361ee;
            color: white;
            border-radius: 8px;
            padding: 12px 20px;
            font-weight: 600;
            width: 100%;
            margin-bottom: 10px;
        }

        .btn-custom:hover {
            background: #3f37c9;
            color: white;
        }

        .feedback-entry {
            background: white;
            padding: 20px;
            margin: 15px 0;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #4361ee;
        }

        .feedback-entry h3 {
            color: #2d3748;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .feedback-entry p {
            margin: 5px 0;
            color: #4a5568;
        }

        .feedback-entry .response {
            color: #28a745;
            font-weight: 500;
        }

        .feedback-entry .no-response {
            color: #dc3545;
            font-weight: 500;
        }

        /* Mobile Toggle Button */
        .toggle-sidebar {
            display: none;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .toggle-sidebar {
                display: block;
                position: fixed;
                top: 1rem;
                left: 1rem;
                z-index: 1001;
                background: #4361ee;
                color: white;
                padding: 0.5rem;
                border-radius: 5px;
                cursor: pointer;
            }

            .container {
                margin: 10px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Toggle Button -->
    <div class="toggle-sidebar d-lg-none">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <h4><i class="fas fa-book-reader"></i> Student Portal</h4>
        <a href="First Page.php"><i class="fas fa-home"></i><span>Dashboard</span></a>
        <a href="view books.php"><i class="fas fa-book"></i> View Available Books</a>
        <a href="Request borrow.php"><i class="fas fa-hand-holding"></i> Borrow a Book</a>
        <a href="request_return.php"><i class="fas fa-undo"></i> Request Return</a>
        <a href="borrowing history.php"><i class="fas fa-history"></i> My Borrowing History</a>
        <a href="search_books.php"><i class="fas fa-search"></i> Search for Books</a>
        <a href="Display Fine.php"><i class="fas fa-wallet"></i> Payment</a>
        <a href="report_lost.php"><i class="fas fa-exclamation-triangle"></i> Report Lost Book</a>
        <a href="profile_settings.php"><i class="fas fa-user-cog"></i> Profile Settings</a>
        <a href="notifications.php">
            <i class="fas fa-bell"></i> Notifications
            <?php if (isset($notifications_count) && $notifications_count > 0): ?>
                <span class="notification-badge"><?php echo $notifications_count; ?></span>
            <?php endif; ?>
        </a>
        <a class="nav-link active" href="#"><i class="fas fa-comment-alt"></i> Feedback</a>
        <a href="../../login.html"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header>Library Feedback</header>

        <div class="container">
            <form action="../../../backend-php/feedback_submission_handler.php" method="post">
                <div class="form-group">
                    <label for="student_name">Name (optional):</label>
                    <input type="text" class="form-control" id="student_name" name="student_name" value="<?php echo htmlspecialchars($studentName); ?>">
                </div>
                
                <div class="form-group">
                    <label for="feedback_text">Feedback:</label>
                    <textarea class="form-control" id="feedback_text" name="feedback_text" rows="5" required></textarea>
                </div>
                
                <button type="submit" class="btn btn-custom">Submit Feedback</button>
            </form>
        </div>

        <div class="container mt-4">
            <h3 class="mb-4">Feedback History</h3>
            <?php
            $sql = "SELECT f.id, f.student_id, f.feedback_text, f.created_at, f.status, r.response_text
                    FROM feedback f
                    LEFT JOIN librarian_responses r ON f.id = r.feedback_id
                    WHERE f.student_id = ?
                    ORDER BY f.created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='feedback-entry'>";
                    echo "<h3>Feedback #" . $row['id'] . "</h3>";
                    echo "<p><strong>Date Submitted:</strong> " . date("M d, Y h:i A", strtotime($row['created_at'])) . "</p>";
                    echo "<p><strong>Feedback:</strong> " . htmlspecialchars($row['feedback_text']) . "</p>";
                    echo "<p><strong>Status:</strong> " . htmlspecialchars($row['status']) . "</p>";
                    echo "<p><strong>Response:</strong> " . ($row['response_text'] ? "<span class='response'>" . htmlspecialchars($row['response_text']) . "</span>" : "<span class='no-response'>No response yet</span>") . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<div class='feedback-entry'>";
                echo "<p>No feedback history available.</p>";
                echo "</div>";
            }
            $stmt->close();
            ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        $(document).ready(function() {
            $('.toggle-sidebar').click(function() {
                $('.sidebar').toggleClass('active');
            });

            // Close sidebar when clicking outside on mobile
            $(document).click(function(event) {
                if (!$(event.target).closest('.sidebar, .toggle-sidebar').length) {
                    $('.sidebar').removeClass('active');
                }
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>