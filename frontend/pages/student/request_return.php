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

$message = "";

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
            $notification_message = "$studentName has requested to return the book '$book_title'.";

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
$stmt_books->bind_param("i", $student_id);
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
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 20px auto;
        }

        select, button {
            width: 100%;
            padding: 15px;
            margin-top: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            background-color: #007bff;
            color: white;
            font-size: 18px;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            text-align: center;
            margin: 20px 0;
        }

        .success {
            color: #28a745;
            padding: 10px;
            border-radius: 5px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .error {
            color: #dc3545;
            padding: 10px;
            border-radius: 5px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
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
        <a class="nav-link active" href="#"><i class="fas fa-undo"></i> Request Return</a>
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
        <header>Request Book Return</header>

        <div class="container">
            <div class="message">
                <?php echo $message; ?>
            </div>

            <?php if (!empty($borrowed_books)): ?>
                <h3>Your Borrowed Books</h3>
                <form method="POST" action="">
                    <label for="book_id">Select Book to Return:</label>
                    <select name="book_id" required>
                        <?php foreach ($borrowed_books as $book): ?>
                            <option value="<?php echo $book['id']; ?>"> <?php echo htmlspecialchars($book['title']); ?> </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Request Return</button>
                </form>
            <?php else: ?>
                <p style="text-align: center; color: #888;">You have no borrowed books.</p>
            <?php endif; ?>
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
