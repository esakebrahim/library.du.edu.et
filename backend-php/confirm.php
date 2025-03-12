<?php
session_start(); // Start the session

// Check if user is logged in, otherwise redirect to login page
if (!isset($_SESSION['name'])) {
    header("Location: ../../login.html");
    exit();
}

$librarianName = $_SESSION['name'];
$Id = $_SESSION['id']; // Retrieve the librarian's ID

include '../../../backend-php/database.php'; // Include database connection

$pendingFeedbackCount = 0;
$sql = "SELECT COUNT(*) AS pending_count FROM feedback WHERE status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$pendingFeedbackCount = $row['pending_count']; 
$stmt->close();

// Fetch unread feedback notifications
$notificationCount = 0;
$sql = "SELECT COUNT(*) AS unread_count FROM feedback WHERE student_id = ? AND status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $Id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$notificationCount = $row['unread_count']; // Get the count of unread notifications
$stmt->close();

// Get librarian's role
$sql_role = "SELECT role_name FROM librarian_roles r 
        JOIN users u ON r.id = u.role_id 
        WHERE u.id = ?";
$stmt_role = $conn->prepare($sql_role);
$stmt_role->bind_param("i", $Id);
$stmt_role->execute();
$stmt_role->bind_result($role_name);
$stmt_role->fetch();
$stmt_role->close();

// Fetch other dashboard details like total books, pending borrow/return requests, and assigned library branches
$sql_books = "SELECT COUNT(*) AS total_books FROM books";
$result_books = $conn->query($sql_books);
$totalBooks = $result_books->fetch_assoc()['total_books'] ?? 0;

$sql_borrow_pending = "SELECT COUNT(*) AS pending_borrow FROM borrow_requests WHERE status = 'borrow_pending'";
$pendingBorrow = $conn->query($sql_borrow_pending)->fetch_assoc()['pending_borrow'] ?? 0;

$sql_return_pending = "SELECT COUNT(*) AS pending_return FROM borrow_requests WHERE status = 'return_pending'";
$pendingReturn = $conn->query($sql_return_pending)->fetch_assoc()['pending_return'] ?? 0;

// Fetch assigned library branches
$sql_branches = "
    SELECT library_branches.name 
    FROM librarian_branches 
    JOIN library_branches ON librarian_branches.library_branch_id = library_branches.id 
    WHERE librarian_branches.librarian_id = ?";
$stmt_branches = $conn->prepare($sql_branches);
$stmt_branches->bind_param("i", $Id);
$stmt_branches->execute();
$result_branches = $stmt_branches->get_result();

$assignedBranches = [];
while ($row = $result_branches->fetch_assoc()) {
    $assignedBranches[] = $row['name'];
}
$stmt_branches->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            display: flex;
            height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #007bff;
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .sidebar h2 {
            margin-bottom: 30px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            width: 100%;
            border-radius: 5px;
            transition: background 0.3s;
            position: relative;
        }
        .sidebar a i {
            margin-right: 10px;
        }
        .sidebar a:hover {
            background: #0056b3;
        }
        .notification-badge {
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 3px 7px;
            font-size: 12px;
            position: absolute;
            right: 15px;
            top: 10px;
        }
        .content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }
        .dashboard-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .card {
            background: white;
            padding: 20px;
            flex: 1 1 calc(25% - 20px);
            margin: 10px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            min-width: 200px;
        }
        .card h3 {
            margin: 10px 0;
        }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #dee2e6;
        }
        th {
            background: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background: #f2f2f2;
        }
        tr:hover {
            background: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Librarian Dashboard</h2>
        <a href="#"><i class="fas fa-home"></i>Home</a>

        <!-- Role-Based Menu Links -->
        <?php
        if ($role_name == 'Cataloging') {
            echo '<a href="../../../backend-php/add_book.php"><i class="fas fa-plus"></i> Add Books</a>';
            echo '<a href="../../../backend-php/view_books.php"><i class="fas fa-book"></i> Manage Books</a>';
            echo '<a href="feedback_display.php">
                    <i class="fas fa-comments-alt"></i> View Feedback';
            
            if ($pendingFeedbackCount > 0) {
                echo '<span class="notification-badge">' . $pendingFeedbackCount . '</span>';
            }
            
            echo '</a>';
            
        } elseif ($role_name == 'Circulation') {
            echo '<a href="../../../backend-php/pending books.php"><i class="fas fa-arrow-right"></i> Issue Book</a>';
            echo '<a href="../../../backend-php/return_book.php"><i class="fas fa-arrow-left"></i> Return Book</a>';
            echo '<a href="../../../backend-php/feedback.php"><i class="fas fa-comment-alt"></i> Manage Feedback</a>';
        } elseif ($role_name == 'Acquisition') {
            echo '<a href="../../../backend-php/manage_acquisitions.php"><i class="fas fa-book-open"></i> Manage Book Acquisitions</a>';
            echo '<a href="../../../backend-php/feedback.php"><i class="fas fa-comment-alt"></i> Manage Feedback</a>';
        }
        ?>

        <a href="../../../backend-php/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="content">
        <h1>Welcome, <?php echo htmlspecialchars($librarianName); ?></h1>
        <div class="dashboard-cards">
            <div class="card">
                <h3>Total Books</h3>
                <p><?php echo $totalBooks; ?></p>
            </div>
            <div class="card">
                <h3>Total Pending Borrow</h3>
                <p><?php echo $pendingBorrow; ?></p>
            </div>
            <div class="card">
                <h3>Total Pending Return</h3>
                <p><?php echo $pendingReturn; ?></p>
            </div>
            <div class="card">
                <h3>Assigned Library Branches</h3>
                <ul>
                    <?php
                    if (!empty($assignedBranches)) {
                        foreach ($assignedBranches as $branch) {
                            echo "<li>" . htmlspecialchars($branch) . "</li>";
                        }
                    } else {
                        echo "<li>No branch assigned</li>";
                    }
                    ?>
                </ul>
            </div>
        </div>

        <div class="table-container">
            <h2>Recent Feedback</h2>
            <table>
                <tr>
                    <th>Student Name</th>
                    <th>Feedback</th>
                    <th>Status</th>
                </tr>
                <?php
             $feedbackQuery = "
             SELECT users.name AS student_name, feedback.feedback_text, feedback.status 
             FROM feedback
             JOIN users ON feedback.student_id = users.id
             WHERE feedback.status = 'pending'
             ORDER BY feedback.created_at DESC 
             LIMIT 5
         ";
                $feedbackResult = $conn->query($feedbackQuery);

                if ($feedbackResult->num_rows > 0) {
                    while ($row = $feedbackResult->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['feedback_text']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No feedback available</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>
