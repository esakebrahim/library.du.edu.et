<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: ../../login.html");
    exit();
}

$studentName = $_SESSION['student_name']; 
$student_id = $_SESSION['student_id']; 

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Books</title>
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

        /* Main Content Adjustments */
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
        }

        .container {
            display: flex;
            width: 100%;
            max-width: 1200px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            margin: 20px auto;
            padding: 20px;
            gap: 20px;
        }

        .section {
            flex: 1;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            background: #ffffff;
            transition: transform 0.2s;
        }

        .section:hover {
            transform: scale(1.02);
        }

        h2 {
            color: #495057;
            margin-top: 0;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
            font-weight: 600;
        }

        .book-container {
            background-color: #f8f9fa;
            border-radius: 8px;
            margin: 10px 0;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .book-container:hover {
            transform: translateY(-5px);
            background-color: #e3f2fd;
        }

        .book-info {
            flex-grow: 1;
            font-size: 14px;
        }

        .book-info span {
            display: block;
            margin: 5px 0;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .no-books {
            text-align: center;
            color: #6c757d;
            font-size: 1.2em;
            margin-top: 20px;
        }

        .available-section, .reserved-section {
            overflow-y: auto;
            max-height: 500px;
            padding-right: 10px;
        }

        .search-box {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }

        .search-box input {
            width: 100%;
            max-width: 400px;
            padding: 10px;
            border: 2px solid #007bff;
            border-radius: 25px;
            outline: none;
            font-size: 16px;
            transition: 0.3s;
        }

        .search-box input:focus {
            border-color: #0056b3;
            box-shadow: 0 0 8px rgba(0, 91, 187, 0.5);
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
        }
    </style>
    <script>
        function confirmBorrow() {
            var rules = "Library Borrowing Rules:\n\n" +
                        "1. Borrow up to 5 books at a time.\n" +
                        "2. Return books within 14 days or pay 1 birr per day.\n" +
                        "3. Handle books carefully; damage or loss must be compensated.\n" +
                        "4. Reserved books must be borrowed within 24 hours.\n" +
                        "5. Violating rules may lead to suspension of borrowing privileges.";
            return confirm(rules + "\n\nDo you accept these rules?");
        }
    </script>
    <script>
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("searchInput").addEventListener("keyup", function () {
        let query = this.value;
        let xhr = new XMLHttpRequest();
        xhr.open("GET", "borrow_search.php?query=" + encodeURIComponent(query), true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                document.getElementById("availableBooks").innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    });
});
</script>

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
        <a class="nav-link active" href="#"><i class="fas fa-hand-holding"></i> Borrow a Book</a>
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
        <header>
            Borrow Books
        </header>

        <div class="container">
            <!-- Reserved Books Section -->
            <div class="section reserved-section">
                <h2>Your Reserved Books</h2>
                <?php
                $reservedQuery = "SELECT DISTINCT b.id, r.reservation_id, b.title, b.author, b.status 
                                  FROM reservation r 
                                  JOIN books b ON r.book_id = b.id 
                                  WHERE r.user_id = ? AND r.status = 'active' AND b.status = 'reserved'";
                $reservedStmt = $conn->prepare($reservedQuery);
                $reservedStmt->bind_param("i", $student_id);
                $reservedStmt->execute();
                $reservedResult = $reservedStmt->get_result();

                if ($reservedResult->num_rows > 0) {
                    while ($row = $reservedResult->fetch_assoc()) {
                        echo "<div class='book-container'>";
                        echo "<div class='book-info'>";
                        echo "<span><strong>Title:</strong> {$row['title']}</span>";
                        echo "<span><strong>Author:</strong> {$row['author']}</span>";
                        echo "<span><strong>Status:</strong> {$row['status']}</span>";
                        echo "</div>";
                        echo "<form action='../../../backend-php/borrow.php' method='post' onsubmit='return confirmBorrow();'>";
                        echo "<input type='hidden' name='user_id' value='{$student_id}'>";
                        echo "<input type='hidden' name='book_id' value='{$row['id']}'>";
                        echo "<input type='hidden' name='reservation_id' value='{$row['reservation_id']}'>";
                        echo "<input type='hidden' name='is_reserved' value='true'>";
                        echo "<input type='submit' value='Borrow' />";
                        echo "</form>";
                        echo "</div>";
                    }
                } else {
                    echo "<p class='no-books'>You have no reserved books.</p>";
                }
                ?>
            </div>
            <!-- Available Books Section -->
            <div class="section available-section">
                <h2>Available Books</h2>
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search by title or author">
                </div>

                <div id="availableBooks"> <!-- This should be outside PHP -->
                    <?php
                    $search = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%";
                    $availableQuery = "SELECT DISTINCT b.id, b.title, b.author, b.status 
                                       FROM books b 
                                       LEFT JOIN borrow_requests br ON b.id = br.book_id 
                                       WHERE b.status = 'available' 
                                       AND (
                                           br.book_id IS NULL 
                                           OR br.status IS NULL
                                           OR br.status = 'return_accept'
                                           OR br.status = 'borrow_reject'
                                       )
                                       AND NOT EXISTS (
                                           SELECT 1 FROM borrow_requests br2 
                                           WHERE br2.book_id = b.id AND br2.status = 'borrow_pending'
                                       )
                                       AND (b.title LIKE ? OR b.author LIKE ?)
                                       ORDER BY b.title ASC";
                    $availableStmt = $conn->prepare($availableQuery);
                    $availableStmt->bind_param("ss", $search, $search);
                    $availableStmt->execute();
                    $availableResult = $availableStmt->get_result();

                    if ($availableResult->num_rows > 0) {
                        while ($row = $availableResult->fetch_assoc()) {
                            echo "<div class='book-container'>";
                            echo "<div class='book-info'>";
                            echo "<span><strong>Title:</strong> {$row['title']}</span>";
                            echo "<span><strong>Author:</strong> {$row['author']}</span>";
                            echo "<span><strong>Status:</strong> {$row['status']}</span>";
                            echo "</div>";
                            echo "<form action='../../../backend-php/borrow.php' method='post' onsubmit='return confirmBorrow();'>";
                            echo "<input type='hidden' name='user_id' value='{$student_id}'>";
                            echo "<input type='hidden' name='book_id' value='{$row['id']}'>";
                            echo "<input type='hidden' name='is_reserved' value='false'>";
                            echo "<input type='submit' value='Borrow' />";
                            echo "</form>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p class='no-books'>All available books are reserved.</p>";
                    }

                    $conn->close();
                    ?>
                </div> <!-- Close the div after PHP ends -->
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