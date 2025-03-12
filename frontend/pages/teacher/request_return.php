<?php
session_start();
include_once '../../../backend-php/database.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['teacher_id'])) {
    die("Please log in to request a book return.");
}

$user_id = $_SESSION['teacher_id'];
$message = "";

// Fetch student name
$sql_user = "SELECT name FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user_name = $user_result->fetch_assoc()['name'];
$stmt_user->close();

// Fetch unread notifications count
$notif_sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? and status='unread'";
$notif_stmt = $conn->prepare($notif_sql);
$notif_stmt->bind_param("i", $user_id);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();
$notif_row = $notif_result->fetch_assoc();
$notification_count = $notif_row['count'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $book_id = $_POST["book_id"];
    $message = ""; // Reset message at the start of POST request handling

    // Fetch book title
    $sql_book = "SELECT title FROM books WHERE id = ?";
    $stmt_book = $conn->prepare($sql_book);
    $stmt_book->bind_param("i", $book_id);
    $stmt_book->execute();
    $book_result = $stmt_book->get_result();
    $book_title = $book_result->fetch_assoc()['title'];
    $stmt_book->close();

    // Update books table to mark the book as 'pending_return'
    $sql_update_book = "UPDATE borrow_requests SET status = 'return_pending' WHERE book_id = ?";
    $stmt_book = $conn->prepare($sql_update_book);
    $stmt_book->bind_param("i", $book_id);

    if ($stmt_book->execute()) {
        // Fetch the library branch of the book
        $sql_branch = "SELECT books.branch_id AS branch_id 
                       FROM books 
                       JOIN librarian_branches ON books.branch_id = librarian_branches.library_branch_id 
                       WHERE books.id = ?";
        $stmt_branch = $conn->prepare($sql_branch);
        $stmt_branch->bind_param("i", $book_id);
        $stmt_branch->execute();
        $result_branch = $stmt_branch->get_result();
        $branch_id = $result_branch->fetch_assoc()['branch_id'];

        // Fetch librarians who work in that branch
        $sql_librarians = "SELECT users.id, users.name 
                           FROM users
                           JOIN librarian_branches ON users.id = librarian_branches.librarian_id 
                           WHERE librarian_branches.library_branch_id = ?";
        $stmt_librarians = $conn->prepare($sql_librarians);
        $stmt_librarians->bind_param("i", $branch_id);
        $stmt_librarians->execute();
        $result_librarians = $stmt_librarians->get_result();

        // Send notifications to librarians
        while ($librarian = $result_librarians->fetch_assoc()) {
            $librarian_id = $librarian['id'];
            $notification_message = "$user_name has requested to return the book '$book_title'.";

            $sql_notify = "INSERT INTO notifications (user_id, message, status) VALUES (?, ?, 'unread')";
            $stmt_notify = $conn->prepare($sql_notify);
            $stmt_notify->bind_param("is", $librarian_id, $notification_message);
            $stmt_notify->execute();
        }

        $message = "<div class='success'>Return request submitted successfully and librarians have been notified.</div>";
    } else {
        $message = "<div class='error'>Error updating book status: " . $stmt_book->error . "</div>";
    }

    $stmt_book->close();
}

// Fetch borrowed books for the user
$borrowed_books = [];
$sql_books = "SELECT DISTINCT books.id, books.title 
FROM books  
JOIN borrow_requests ON books.id = borrow_requests.book_id  
LEFT Join lost_books on lost_books.book_id=books.id
WHERE borrow_requests.user_id = ?  and books.status='checked_out' and lost_books.id is null
  AND (borrow_requests.status = 'borrow_accept' or borrow_requests.status='return_reject')";

$stmt_books = $conn->prepare($sql_books);
$stmt_books->bind_param("i", $user_id);
$stmt_books->execute();
$result = $stmt_books->get_result();
while ($row = $result->fetch_assoc()) {
    $borrowed_books[] = $row;
}
$stmt_books->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Book Return</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

        .content-wrapper {
            background: var(--white);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: var(--dark);
            margin-bottom: 1.5rem;
            font-size: 1.875rem;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--secondary);
            font-weight: 500;
        }

        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            color: var(--dark);
            background-color: var(--white);
            transition: var(--transition);
        }

        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        button {
            background-color: var(--primary);
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
        }

        button:hover {
            background-color: var(--primary-dark);
        }

        .success {
            color: var(--success);
            padding: 1rem;
            border-radius: 0.5rem;
            background-color: rgba(34, 197, 94, 0.1);
            margin-bottom: 1rem;
        }

        .error {
            color: var(--danger);
            padding: 1rem;
            border-radius: 0.5rem;
            background-color: rgba(239, 68, 68, 0.1);
            margin-bottom: 1rem;
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

        @media (max-width: 640px) {
            .container {
                padding: 1rem;
            }

            .content-wrapper {
                padding: 1.5rem;
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
        <a href="feedback_submission.php">
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
        <div class="content-wrapper">
            <h2>Request Book Return</h2>
            
            <div class="message">
                <?php echo $message; ?>
            </div>

            <?php if (!empty($borrowed_books)): ?>
                <div class="form-group">
                    <form method="POST" action="">
                        <label for="book_id">Select Book to Return:</label>
                        <select name="book_id" required>
                            <?php foreach ($borrowed_books as $book): ?>
                                <option value="<?php echo $book['id']; ?>">
                                    <?php echo htmlspecialchars($book['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Request Return</button>
                    </form>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: var(--secondary);">You have no borrowed books.</p>
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