<?php
session_start();
include_once '../../../backend-php/database.php';

$librarian_id = $_SESSION['librarian_id'];
// Check if user is logged in
if (!isset($_SESSION['librarian_id'])) {
    die("Access denied. You must log in as an admin to view this page.");
}

// Get librarian's role
$sql_role = "SELECT role_name FROM librarian_roles r JOIN users u ON r.id = u.role_id WHERE u.id = ?";
$stmt_role = $conn->prepare($sql_role);
$stmt_role->bind_param("i", $librarian_id);
$stmt_role->execute();
$stmt_role->bind_result($role_name);
$stmt_role->fetch();
$stmt_role->close();

// Get notifications count
$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND status = 'unread'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $librarian_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications_count = $result->fetch_assoc()['unread_count'];
$stmt->close();

// Get pending feedback count
$pendingFeedbackCount = 0;
$sql = "SELECT COUNT(*) AS pending_count FROM feedback WHERE status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$pendingFeedbackCount = $row['pending_count']; 
$stmt->close();

$sql_branch = "SELECT library_branch_id FROM librarian_branches WHERE librarian_id = ?";
$stmt_branch = $conn->prepare($sql_branch);
$stmt_branch->bind_param("i", $librarian_id);
$stmt_branch->execute();
$stmt_branch->store_result();

if ($stmt_branch->num_rows == 0) {
    die("<div class='error'>Access Denied: You are not assigned to a library branch.</div>");
}
$stmt_branch->bind_result($librarian_branch);
$stmt_branch->fetch();
$stmt_branch->close();
$message = "";

// Handle confirmation or rejection of return requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $book_id = $_POST["book_id"];
    $action = $_POST["action"];

    // Fetch the associated borrow request
    $sql_borrow_request = "SELECT user_id FROM borrow_requests WHERE book_id = ? AND status = 'return_pending'";
    $stmt_borrow = $conn->prepare($sql_borrow_request);
    $stmt_borrow->bind_param("i", $book_id);
    $stmt_borrow->execute();
    $borrow_result = $stmt_borrow->get_result();
    
    if ($borrow_result->num_rows > 0) {
        $borrow_data = $borrow_result->fetch_assoc();
        $user_id = $borrow_data['user_id'];
    } else {
        $message = "<div class='error'>No borrow request found for this book.</div>";
    }

    if ($action == "confirm" && isset($user_id)) {
        // Update the book status to 'available'
        $sql_update_book = "UPDATE books SET status = 'available' WHERE id = ?";
        $stmt_book = $conn->prepare($sql_update_book);
        $stmt_book->bind_param("i", $book_id);
        $stmt_book->execute();

        // Add return date to borrow request and update return_status
        $return_date = date('Y-m-d'); // Current date as return date
        $sql_update_borrow = "UPDATE borrow_requests SET return_date = CURRENT_DATE, status = 'return_accept' WHERE book_id = ? AND user_id = ?";
        $stmt_borrow_update = $conn->prepare($sql_update_borrow);
        $stmt_borrow_update->bind_param("ii", $book_id, $user_id);
        $stmt_borrow_update->execute();

        $log_action = "Confirmed return of book ID $book_id";
        $sql_log = "INSERT INTO librarian_actions_log (librarian_id, action, book_id, timestamp) VALUES (?, ?, ?, NOW())";
        $stmt_log = $conn->prepare($sql_log);
        $stmt_log->bind_param("isi", $librarian_id, $log_action, $book_id);
        $stmt_log->execute();
        
        // Fetch the book title
$sql_book_title = "SELECT title FROM books WHERE id = ?";
$stmt_title = $conn->prepare($sql_book_title);
$stmt_title->bind_param("i", $book_id);
$stmt_title->execute();
$stmt_title->bind_result($book_title);
$stmt_title->fetch();
$stmt_title->close();

// Notify the student with book title
$notification_message = "Your return request for the book '$book_title' has been confirmed.";
$sql_notification = "INSERT INTO notifications (user_id, message, status) VALUES (?, ?, 'unread')";
$stmt_notification = $conn->prepare($sql_notification);
$stmt_notification->bind_param("is", $user_id, $notification_message);
$stmt_notification->execute();


        $message = "<div class='success'>Return request confirmed successfully.</div>";
    } elseif ($action == "reject") {
        // Update the return_status of borrow request to 'return_reject'
        $sql_update_borrow = "UPDATE borrow_requests SET status = 'return_reject' WHERE book_id = ? AND user_id = ?";
        $stmt_borrow_update = $conn->prepare($sql_update_borrow);
        $stmt_borrow_update->bind_param("ii", $book_id, $user_id);
        $stmt_borrow_update->execute();

        $sql_update_borro = "UPDATE books SET status = 'checked_out' WHERE id = ?";
        $stmt_borrow_updat = $conn->prepare($sql_update_borro);
        $stmt_borrow_updat->bind_param("i", $book_id);
        $stmt_borrow_updat->execute();

        $log_action = "Rejected return of book ID $book_id";
        $sql_log = "INSERT INTO librarian_actions_log (librarian_id, action, book_id, timestamp) VALUES (?, ?, ?, NOW())";
        $stmt_log = $conn->prepare($sql_log);
        $stmt_log->bind_param("isi", $librarian_id, $log_action, $book_id);
        $stmt_log->execute();


        // Fetch the book title
$sql_book_title = "SELECT title FROM books WHERE id = ?";
$stmt_title = $conn->prepare($sql_book_title);
$stmt_title->bind_param("i", $book_id);
$stmt_title->execute();
$stmt_title->bind_result($book_title);
$stmt_title->fetch();
$stmt_title->close();

// Notify the student with book title
$notification_message = "Your return request for the book '$book_title' has been rejected.";
$sql_notification = "INSERT INTO notifications (user_id, message, status) VALUES (?, ?, 'unread')";
$stmt_notification = $conn->prepare($sql_notification);
$stmt_notification->bind_param("is", $user_id, $notification_message);
$stmt_notification->execute();


        $message = "<div class='success'>Return request rejected successfully.</div>";
    }
}

// Fetch books with pending return requests
$pending_books = [];
$sql_books = "SELECT DISTINCT books.id, books.title FROM books JOIN borrow_requests ON books.id = borrow_requests.book_id 
 WHERE borrow_requests.status = 'return_pending' AND books.branch_id = ?";
$stmt_books = $conn->prepare($sql_books);
$stmt_books->bind_param("i", $librarian_branch);
$stmt_books->execute();
$result = $stmt_books->get_result();
while ($row = $result->fetch_assoc()) {
    $pending_books[] = $row;
}
$stmt_books->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Return Requests</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --secondary: #64748b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #0ea5e9;
            --white: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-800: #1e293b;
            --dark-gradient: linear-gradient(135deg, #2a3f54 0%, #1a2942 100%);
            --sidebar-item-hover: rgba(255, 255, 255, 0.08);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--gray-50);
            min-height: 100vh;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        .d-flex {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .sidebar {
            min-height: 100vh;
            background: var(--dark-gradient);
            color: var(--white);
            padding: 1.5rem;
            width: 280px;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            transition: var(--transition);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar h2 {
            font-size: 1.25rem;
            font-weight: 700;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            color: var(--white);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            letter-spacing: 0.5px;
        }

        .sidebar a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            padding: 0.875rem 1.25rem;
            display: flex;
            align-items: center;
            border-radius: 0.75rem;
            margin-bottom: 0.5rem;
            transition: var(--transition);
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .sidebar a:hover {
            background: var(--sidebar-item-hover);
            color: var(--white);
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .sidebar a i {
            width: 1.5rem;
            margin-right: 1rem;
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .notification-badge {
            background: linear-gradient(135deg, var(--danger), #dc2626);
            color: var(--white);
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            margin-left: auto;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
        }

        .content {
            margin-left: 280px;
            padding: 2rem;
            transition: var(--transition);
            width: calc(100% - 280px);
            min-height: 100vh;
        }

        .content h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--primary-light);
            background: linear-gradient(to right, var(--gray-800), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .book-card {
            background: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: var(--transition);
        }

        .book-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 12px -2px rgba(0, 0, 0, 0.1);
        }

        .book-title {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--gray-800);
        }

        .button-group {
            display: flex;
            gap: 0.75rem;
        }

        .confirm-button, .reject-button {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .confirm-button {
            background: var(--success);
            color: white;
        }

        .confirm-button:hover {
            background: #0d9488;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .reject-button {
            background: var(--danger);
            color: white;
        }

        .reject-button:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .message {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .mobile-menu-toggle {
            display: none;
        }

        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .content {
                margin-left: 0;
                width: 100%;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .mobile-menu-toggle {
                display: block;
                position: fixed;
                top: 1rem;
                right: 1rem;
                background: var(--primary);
                color: var(--white);
                padding: 0.75rem;
                border-radius: 0.5rem;
                cursor: pointer;
                z-index: 1001;
            }
        }

        @media (max-width: 768px) {
            .content {
                padding: 1rem;
            }

            .book-card {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .button-group {
                width: 100%;
                justify-content: center;
            }
        }

        .dropdown {
            position: relative;
        }

        .dropdown-toggle {
            width: 100%;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            padding: 0.875rem 1.25rem;
            display: flex;
            align-items: center;
            border-radius: 0.75rem;
            margin-bottom: 0.5rem;
            transition: var(--transition);
            font-weight: 500;
            cursor: pointer;
        }

        .dropdown-toggle:hover {
            background: var(--sidebar-item-hover);
            color: var(--white);
            transform: translateX(4px);
        }

        .dropdown-toggle i {
            width: 1.5rem;
            margin-right: 1rem;
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .dropdown-toggle .fa-chevron-down {
            margin-left: auto;
            margin-right: 0;
            transition: transform 0.3s ease;
        }

        .dropdown.active .fa-chevron-down {
            transform: rotate(180deg);
        }

        .submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .dropdown.active .submenu {
            max-height: 200px;
        }

        .submenu li a {
            padding: 0.5rem 1.25rem 0.5rem 3rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            transition: var(--transition);
        }

        .submenu li a:hover {
            background: var(--sidebar-item-hover);
            color: var(--white);
            transform: translateX(4px);
        }

        .submenu li a i {
            width: 1.25rem;
            margin-right: 0.75rem;
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
        }
    </style>
</head>
<body>
    <div class="mobile-menu-toggle d-lg-none">
        <i class="fas fa-bars"></i>
    </div>
    <div class="d-flex">
        <div class="sidebar">
            <h2>Librarian Dashboard</h2>
            <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>

            <?php
            if ($role_name == 'Cataloging') {
                echo '<a href="add_category.php"><i class="fas fa-plus"></i> Add Category</a>';
                echo '<a href="add_book.php"><i class="fas fa-plus"></i> Add Books</a>';
                echo '<a href="../../../backend-php/view_books.php"><i class="fas fa-book"></i> Manage Books</a>';
            } elseif ($role_name == 'Circulation') {
                echo '<a href="../../../backend-php/pending books.php"><i class="fas fa-arrow-right"></i> Issue Book</a>';
                echo '<a href="confirm return.php" class="active"><i class="fas fa-arrow-left"></i> Return Book</a>';
                echo '<a href="manage_extensions.php"><i class="fas fa-clock"></i> Manage Extensions</a>';
                echo '<div class="dropdown">';
                echo '<div class="dropdown-toggle">';
                echo '<i class="fas fa-money-bill-wave"></i> Payment';
                echo '<i class="fas fa-chevron-down"></i>';
                echo '</div>';
                echo '<ul class="submenu">';
                echo '<li><a href="confirm_fine.php"><i class="fas fa-check-circle"></i> Confirm Fine</a></li>';
                echo '<li><a href="enforce_fine.php"><i class="fas fa-gavel"></i> Enforce Fine</a></li>';
                echo '</ul>';
                echo '</div>';
            } elseif ($role_name == 'Acquisition') {
                echo '<a href=""><i class="fas fa-book-open"></i> Manage Acquisitions</a>';
            }

            if ($pendingFeedbackCount > 0) {
                echo '<a href="feedback display.php"><i class="fas fa-comments"></i> View Feedback <span class="badge badge-danger float-right">' . $pendingFeedbackCount . '</span></a>';
            }
            ?>
            <a href="search_books.php"><i class="fas fa-search"></i> Search Book</a>

            <a href="notifications.php"><i class="fas fa-bell"></i> Notifications
            <?php if ($notifications_count > 0): ?>
                <span class="notification-badge"><?php echo $notifications_count; ?></span>
            <?php endif; ?>
            </a>
            <a href="../../login.html"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="content">
            <h1>Confirm Return Requests</h1>
            
            <?php if (!empty($message)): ?>
                <div class="message <?php echo strpos($message, 'success') !== false ? 'success' : 'error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($pending_books)): ?>
                <div class="books-container">
                    <?php foreach ($pending_books as $book): ?>
                        <div class="book-card">
                            <span class="book-title"><?php echo htmlspecialchars($book['title']); ?></span>
                            <div class="button-group">
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <button type="submit" name="action" value="confirm" class="confirm-button">
                                        <i class="fas fa-check"></i> Confirm
                                    </button>
                                </form>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <button type="submit" name="action" value="reject" class="reject-button">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="book-card">
                    <p style="text-align: center; color: var(--secondary); margin: 0;">No pending return requests.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const menuToggle = document.querySelector('.mobile-menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            const content = document.querySelector('.content');
            
            if (menuToggle) {
                menuToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('active');
                    content.classList.toggle('sidebar-active');
                });
            }

            // Dropdown functionality
            const dropdownToggle = document.querySelector('.dropdown-toggle');
            if (dropdownToggle) {
                dropdownToggle.addEventListener('click', () => {
                    const dropdown = dropdownToggle.closest('.dropdown');
                    dropdown.classList.toggle('active');
                });
            }

            // Close sidebar when clicking outside
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 1024) {
                    if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                        sidebar.classList.remove('active');
                        content.classList.remove('sidebar-active');
                    }
                }
            });
        });
    </script>
</body>
</html>
