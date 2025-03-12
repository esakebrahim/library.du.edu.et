<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: ../../login.html");
    exit();
}

$student_id = $_SESSION['student_id'];
$studentName = $_SESSION['student_name'];

include '../../../backend-php/database.php';

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

// Get overdue fines
$overdueQuery = "SELECT b.id AS book_id, b.title, br.due_date, br.user_id,
                 DATEDIFF(NOW(), br.due_date) AS overdue_days,
                 CASE WHEN DATEDIFF(NOW(), br.due_date) > 0 THEN DATEDIFF(NOW(), br.due_date) * 1 ELSE 0 END AS overdue_fine
                 FROM borrow_requests br
                 JOIN books b ON br.book_id = b.id
                 WHERE br.user_id = ? 
                 AND br.status = 'borrow_accept'
                 AND b.status != 'lost'";

$stmt = $conn->prepare($overdueQuery);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$overdueResults = $stmt->get_result();

while ($row = $overdueResults->fetch_assoc()) {
    $book_id = $row['book_id'];
    $student_id = $row['user_id'];
    $overdue_days = $row['overdue_days'];
    $overdue_fine = $row['overdue_fine'];

    if ($overdue_fine > 0) { 
        // Check if a fine for this book and student already exists
        $checkFineQuery = "SELECT id, amount FROM payments WHERE book_id = ? AND user_id = ? AND reason = 'overdue' AND status = 'pending'";
        $checkStmt = $conn->prepare($checkFineQuery);
        $checkStmt->bind_param("ii", $book_id, $student_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            // Fine exists, update the amount
            $row = $checkResult->fetch_assoc();
            $existing_fine_id = $row['id'];

            $updateQuery = "UPDATE payments SET amount = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("di", $overdue_fine, $existing_fine_id);
            $updateStmt->execute();
        } else {
            // No fine exists, insert a new one
            $insertQuery = "INSERT INTO payments (user_id, book_id, amount, reason, status) VALUES (?, ?, ?, 'overdue', 'pending')";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("iid", $student_id, $book_id, $overdue_fine);
            $insertStmt->execute();
        }
    }
}

// Get manual fines
$fineQuery = "SELECT f.amount, f.reason, b.title, f.paid FROM payments f
              JOIN books b ON f.book_id = b.id
              WHERE f.user_id = ? AND f.status = 'pending'";
$stmt = $conn->prepare($fineQuery);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$fineResults = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Fines</title>
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
            max-width: 1000px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border: 1px solid #dee2e6;
        }

        th {
            background-color: #007bff;
            color: white;
            font-weight: 600;
            white-space: nowrap;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e9ecef;
        }

        .fine-amount {
            font-weight: bold;
            color: #dc3545;
        }

        .reason {
            font-style: italic;
        }

        .status-paid {
            color: #28a745;
            font-weight: 600;
        }

        .status-unpaid {
            color: #dc3545;
            font-weight: 600;
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

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
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
        <a class="nav-link active" href="#"><i class="fas fa-wallet"></i> Payment</a>
        <a href="report_lost.php"><i class="fas fa-exclamation-triangle"></i> Report Lost Book</a>
        <a href="profile_settings.php"><i class="fas fa-user-cog"></i> Profile Settings</a>
        <a href="notifications.php">
            <i class="fas fa-bell"></i> Notifications
            <?php if (isset($notifications_count) && $notifications_count > 0): ?>
                <span class="notification-badge"><?php echo $notifications_count; ?></span>
            <?php endif; ?>
        </a>
        <a href="feedback_submission.php?notification_id=<?php echo $notificationId ?? ''; ?>">
            <i class="fas fa-comment-alt"></i> Feedback
            <?php if (isset($feedbackCount) && $feedbackCount > 0): ?>
                <span class="notification-badge"><?php echo $feedbackCount; ?></span>
            <?php endif; ?>
        </a>
        <a href="../../login.html"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header>Payment Details</header>

        <div class="container">
            <table>
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Reason</th>
                        <th>Amount (Birr)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($fineResults->num_rows > 0) {
                        while ($row = $fineResults->fetch_assoc()) { ?>
                            <tr>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td class="reason"><?= ucfirst(htmlspecialchars($row['reason'])) ?></td>
                                <td class="fine-amount"><?= htmlspecialchars($row['amount']) ?></td>
                                <td>
                                    <?php if ($row['paid']): ?>
                                        <span class="status-paid">Paid</span>
                                    <?php else: ?>
                                        <span class="status-unpaid">Unpaid</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No fines found.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
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
