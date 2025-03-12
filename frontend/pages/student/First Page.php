<?php
session_start(); 

if (!isset($_SESSION['student_id'])) {
    header("Location: ../../login.html");
    exit();
}

$studentName = $_SESSION['student_name']; 
$student_id = $_SESSION['student_id']; 

include '../../../backend-php/database.php'; 

$user_check_sql = "SELECT type FROM users WHERE id = ?";
$stmt = $conn->prepare($user_check_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$user_type = $result->fetch_assoc()['type'] ?? '';

if ($user_type !== 'student') {
    header("Location: ../../login.html");
    echo "Only students can Login to this page.";
}

$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND status = 'unread' and type='general'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications_count = $result->fetch_assoc()['unread_count'];
$stmt->close();

// First, get the count of unread feedback notifications
$feedbackCount = 0;
$sql = "SELECT COUNT(*) AS feedback_count FROM notifications WHERE user_id = ? AND status = 'unread' AND type = 'feedback'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$feedbackCount = $row['feedback_count'] ?? 0;
$stmt->close();

// Second, get the latest unread notification ID (for marking as read when clicked)
$notificationId = null;
$sql = "SELECT id FROM notifications WHERE user_id = ? AND status = 'unread' AND type = 'feedback' LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$notificationId = $row['id'] ?? null;
$stmt->close();


// Count Borrowed Books
$borrowedBooksSql = "SELECT distinct book_id, COUNT(*) AS count FROM borrow_requests WHERE user_id = ? and( status='borrow_accept' or status='return_reject') ";
$stmt = $conn->prepare($borrowedBooksSql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$borrowedResult = $stmt->get_result();
$borrowedCount = $borrowedResult->fetch_assoc()['count'] ?? 0;

// Count Reserved Books
$reservedBooksSql = "SELECT COUNT(*) AS count FROM reservation WHERE user_id = ? and status='active'";
$stmt = $conn->prepare($reservedBooksSql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$reservedResult = $stmt->get_result();
$reservedCount = $reservedResult->fetch_assoc()['count'] ?? 0;

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --light-bg: #f8f9fa;
            --dark-text: #2b2d42;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 0;
            color: var(--dark-text);
        }

        /* Sidebar Styling */
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding-top: 1.5rem;
            color: white;
            transition: var(--transition);
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
            transition: var(--transition);
            margin: 0.2rem 0.8rem;
            border-radius: 10px;
        }

        .sidebar a i, .sidebar a svg {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }

        .notification-badge {
            background-color: var(--warning-color);
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
            padding: 2rem;
            transition: var(--transition);
        }

        .welcome {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .status-card {
            background: linear-gradient(135deg, var(--accent-color), var(--success-color));
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            overflow: hidden;
            height: 100%;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            font-weight: 600;
            padding: 1.25rem;
            background: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-body {
            padding: 1.5rem;
        }

        .btn {
            border-radius: 10px;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-secondary {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-info {
            background: var(--accent-color);
            border-color: var(--accent-color);
        }

        .btn-warning {
            background: var(--warning-color);
            border-color: var(--warning-color);
            color: white;
        }

        /* Security Card Styles */
        .security-card {
            background: white;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }

        .security-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .security-icon-wrapper {
            width: 48px;
            height: 48px;
            background: var(--primary-color);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .security-icon-wrapper i {
            font-size: 1.5rem;
            color: white;
        }

        .security-info h5 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark-text);
        }

        .auth-method {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.75rem;
            background: var(--light-bg);
            border-radius: 20px;
            font-size: 0.85rem;
            color: var(--dark-text);
        }

        .auth-method i {
            margin-right: 0.35rem;
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .security-card .card-body {
                flex-direction: column;
                text-align: center;
            }

            .security-actions {
                flex-direction: column;
                margin-top: 1rem;
            }

            .auth-methods {
                margin-bottom: 1rem;
                margin-right: 0 !important;
            }

            .security-info {
                margin-left: 0 !important;
                margin-top: 0.75rem;
            }

            .security-icon-wrapper {
                margin: 0 auto;
            }
        }

        /* Responsive Design */
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
                background: var(--primary-color);
                color: white;
                padding: 0.5rem;
                border-radius: 5px;
                cursor: pointer;
            }
        }

        @media (max-width: 768px) {
            .welcome {
                font-size: 1.5rem;
                padding: 1rem;
            }

            .card {
                margin-bottom: 1rem;
            }

            .main-content {
                padding: 1rem;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card {
            animation: fadeIn 0.6s ease-out;
            animation-fill-mode: both;
        }

        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }
        .card:nth-child(4) { animation-delay: 0.4s; }
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
    <a class="nav-link active" href="#"><i class="fas fa-home"></i><span>Dashboard</span></a>
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
        <?php if ($notifications_count > 0): ?>
            <span class="notification-badge"><?php echo $notifications_count; ?></span>
        <?php endif; ?>
    </a>
    <a href="feedback_submission.php?notification_id=<?php echo $notificationId; ?>">
        <i class="fas fa-comment-alt"></i> Feedback
        <?php if ($feedbackCount > 0): ?>
            <span class="notification-badge"><?php echo $feedbackCount; ?></span>
        <?php endif; ?>
    </a>
    <a href="../../login.html"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="container-fluid">
        <div class="welcome">
            <i class="fas fa-hand-wave"></i> Welcome back, <?php echo htmlspecialchars($studentName); ?>!
        </div>

        <div class="status-card">
            <h5><i class="fas fa-info-circle"></i> Current Status</h5>
            <?php if ($borrowedCount > 0 || $reservedCount > 0): ?>
                <p class="mb-0">You currently have <strong><?php echo $borrowedCount; ?></strong> borrowed books and <strong><?php echo $reservedCount; ?></strong> reserved books.</p>
            <?php else: ?>
                <p class="mb-0">You have no borrowed or reserved books at this time.</p>
            <?php endif; ?>
        </div>

        <!-- Security Status Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card security-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="security-icon-wrapper">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="security-info ml-3">
                                <h5 class="mb-1">Two-Factor Authentication</h5>
                                <p class="mb-0 text-muted">Enhance your account security</p>
                            </div>
                        </div>
                        <div class="security-actions d-flex align-items-center">
                            <div class="auth-methods mr-3">
                                <span class="auth-method">
                                    <i class="fas fa-envelope"></i> Email
                                </span>
                                <span class="auth-method ml-2">
                                    <i class="fas fa-qrcode"></i> Auth App
                                </span>
                            </div>
                            <a href="../two_factor_settings.php" class="btn btn-primary btn-sm">Configure 2FA</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- View Books -->
            <div class="col-lg-6 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="fas fa-books fa-2x text-primary"></i>
                        <h5 class="mt-2">Browse Library Collection</h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <p class="flex-grow-1">Explore our extensive collection of books, journals, and digital resources.</p>
                        <a href="view books.php" class="btn btn-primary mt-auto">Browse Books</a>
                    </div>
                </div>
            </div>

            <!-- Borrowing History -->
            <div class="col-lg-6 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="fas fa-history fa-2x text-secondary"></i>
                        <h5 class="mt-2">Borrowing History</h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <p class="flex-grow-1">Track your borrowing history and manage current loans.</p>
                        <a href="borrowing history.php" class="btn btn-secondary mt-auto">View History</a>
                    </div>
                </div>
            </div>

            <!-- Search Books -->
            <div class="col-lg-6 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="fas fa-search fa-2x text-info"></i>
                        <h5 class="mt-2">Advanced Search</h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <p class="flex-grow-1">Find books quickly using our powerful search features.</p>
                        <a href="search_books.php" class="btn btn-info mt-auto">Search Catalog</a>
                    </div>
                </div>
            </div>

            <!-- Profile Settings -->
            <div class="col-lg-6 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="fas fa-user-cog fa-2x text-warning"></i>
                        <h5 class="mt-2">Account Settings</h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <p class="flex-grow-1">Manage your account preferences and personal information.</p>
                        <a href="profile_settings.php" class="btn btn-warning mt-auto">Update Profile</a>
                    </div>
                </div>
            </div>
        </div>
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