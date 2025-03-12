<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: ../../login.html");
    exit();
}

include '../../../backend-php/database.php';

$student_id = $_SESSION['student_id'];
$studentName = $_SESSION['student_name'];
$message = "";

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'];
    $description = $_POST['description'];
    
    // Check if there is a pending overdue payment for this book and student
    $sql_check = "SELECT id FROM payments WHERE user_id = ? AND book_id = ? AND reason = 'overdue'";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $student_id, $book_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Delete the overdue payment row so that the student is only charged for the lost book
        $sql_delete = "DELETE FROM payments WHERE user_id = ? AND book_id = ? AND reason = 'overdue'";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("ii", $student_id, $book_id);
        $stmt_delete->execute();
        $stmt_delete->close();
    }
    $stmt_check->close();
    
    // Update borrow_requests status to "lost" for the selected book
    $sqls = "UPDATE borrow_requests 
             SET status = 'lost' 
             WHERE book_id = ? AND user_id = ? AND status = 'borrow_accept'";
    $stmts = $conn->prepare($sqls);
    $stmts->bind_param("ii", $book_id, $student_id);
    $stmts->execute();
    $stmts->close();
    
    // Insert lost book report into lost_books table with status 'pending'
    $sql = "INSERT INTO lost_books (user_id, book_id, description, status) VALUES (?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $student_id, $book_id, $description);
    if ($stmt->execute()) {
        $message = "Lost book report submitted successfully.";
    } else {
        $message = "Error submitting report.";
    }
    $stmt->close();
}

// Fetch borrowed books for the student that are currently accepted (borrow_accept)
$sql = "SELECT b.id, b.title 
        FROM books b 
        INNER JOIN borrow_requests br ON b.id = br.book_id 
        WHERE br.user_id = ? AND br.status = 'borrow_accept'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$books = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Lost Book</title>
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
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 10px 15px;
        }

        .form-control:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
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
        <a class="nav-link active" href="#"><i class="fas fa-exclamation-triangle"></i> Report Lost Book</a>
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
        <header>Report Lost Book</header>

        <div class="container">
            <?php if ($message): ?>
                <div class="alert alert-info">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label for="book_id">Select Borrowed Book:</label>
                    <select class="form-control" id="book_id" name="book_id" required>
                        <option value="">-- Select a Book --</option>
                        <?php foreach ($books as $book): ?>
                            <option value="<?php echo $book['id']; ?>">
                                <?php echo htmlspecialchars($book['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Describe the Situation:</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-danger">Submit Report</button>
                    <a href="First Page.php" class="btn btn-secondary ml-2">Cancel</a>
                </div>
            </form>
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
