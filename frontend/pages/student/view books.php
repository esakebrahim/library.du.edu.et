<?php
session_start(); 

if (!isset($_SESSION['student_id'])) {
    header("Location: ../../login.html");
    exit();
}

$studentName = $_SESSION['student_name']; 
$student_id = $_SESSION['student_id']; 

include '../../../backend-php/database.php';

// Get notification counts (same as in First Page.php)
$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND status = 'unread' and type='general'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications_count = $result->fetch_assoc()['unread_count'];
$stmt->close();

// Get feedback counts
$feedbackCount = 0;
$sql = "SELECT COUNT(*) AS feedback_count FROM notifications WHERE user_id = ? AND status = 'unread' AND type = 'feedback'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$feedbackCount = $row['feedback_count'] ?? 0;
$stmt->close();

// Get notification ID
$notificationId = null;
$sql = "SELECT id FROM notifications WHERE user_id = ? AND status = 'unread' AND type = 'feedback' LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$notificationId = $row['id'] ?? null;
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Books</title>
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

        .sidebar a i {
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

        .container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .search-box {
            margin-bottom: 2rem;
        }

        .search-box input {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            transition: var(--transition);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 1rem;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        th {
            background-color: var(--light-bg);
            color: var(--dark-text);
            font-weight: 600;
        }

        tr:hover {
            background-color: var(--light-bg);
        }

        .action-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
        }

        .action-btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
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
            .container {
                padding: 1rem;
            }

            .main-content {
                padding: 1rem;
            }

            table {
                display: block;
                overflow-x: auto;
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
        <a class="nav-link active" href="#"><i class="fas fa-home"></i><span>Dashboard</span></a>
        <a href="view books.php" class="active"><i class="fas fa-book"></i> View Available Books</a>
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
        <div class="container">
            <h2 class="mb-4">Available Books</h2>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search by title or author..." onkeyup="searchBooks()">
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="bookTable">
                        <?php
                        $sql = "SELECT books.id, books.title, books.author FROM books
                                LEFT JOIN borrow_requests ON books.id = borrow_requests.book_id 
                                WHERE books.status='available' 
                                AND (borrow_requests.book_id IS NULL 
                                OR borrow_requests.status='borrow_reject' 
                                OR borrow_requests.status='return_accept')";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                        <td>{$row['id']}</td>
                                        <td>{$row['title']}</td>
                                        <td>{$row['author']}</td>
                                        <td>
                                            <form method='POST' action='reserve_book.php'>
                                                <input type='hidden' name='id' value='{$row['id']}'>
                                                <input type='submit' class='action-btn' value='Reserve' />
                                            </form>
                                        </td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No available books</td></tr>";
                        }

                        $conn->close();
                        ?>
                    </tbody>
                </table>
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

        // Search functionality
        function searchBooks() {
            let query = document.getElementById("searchInput").value;
            let xhr = new XMLHttpRequest();
            xhr.open("GET", "search books.php?query=" + query, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById("bookTable").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }
    </script>
</body>
</html>