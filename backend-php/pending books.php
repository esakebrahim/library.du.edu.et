<?php
session_start();
include_once 'database.php'; // Ensure this file has the $conn variable properly set up
$librarian_id = $_SESSION['librarian_id'];
// Check if user is logged in
if (!isset($_SESSION['librarian_id'])) {
    die("Access denied. You must log in as an admin to view this page.");
}
$librarian_id = $_SESSION['librarian_id'];

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

$pendingQuery = "
    SELECT b.id AS book_id, b.title, b.author, br.id AS request_id, br.status, u.name AS user_name 
    FROM books b 
    JOIN borrow_requests br ON b.id = br.book_id 
    JOIN users u ON br.user_id = u.id 
    WHERE br.status = 'borrow_pending' AND b.branch_id = ?
";
$pendingStmt = $conn->prepare($pendingQuery);
$pendingStmt->bind_param("i", $librarian_branch);
$pendingStmt->execute();
$pendingResult = $pendingStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Borrow Requests</title>
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

        table {
            width: 100%;
            background: var(--white);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            margin-top: 2rem;
        }

        th {
            background: linear-gradient(to right, var(--gray-800), #334155);
            color: var(--white);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1.25rem;
        }

        td {
            padding: 1.25rem;
            border-bottom: 1px solid var(--gray-100);
            color: var(--gray-800);
            font-size: 0.875rem;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background: linear-gradient(to right, var(--gray-50), var(--white));
            transform: scale(1.01);
            transition: var(--transition);
        }

        .confirm-button, .reject-button {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
            font-size: 0.875rem;
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
            margin-left: 0.5rem;
        }

        .reject-button:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
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

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }

        .sidebar a.active {
            background: var(--sidebar-item-hover);
            color: var(--white);
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
            <a href="../frontend/pages/librarian/dashboard.php"><i class="fas fa-home"></i> Home</a>

            <?php
            if ($role_name == 'Cataloging') {
                echo '<a href="../frontend/pages/librarian/add_category.php"><i class="fas fa-plus"></i> Add Category</a>';
                echo '<a href="add_book.php"><i class="fas fa-plus"></i> Add Books</a>';
                echo '<a href="view_books.php"><i class="fas fa-book"></i> Manage Books</a>';
            } elseif ($role_name == 'Circulation') {
                echo '<a href="pending books.php" class="active"><i class="fas fa-arrow-right"></i> Issue Book</a>';
                echo '<a href="../frontend/pages/librarian/confirm return.php"><i class="fas fa-arrow-left"></i> Return Book</a>';
                echo '<a href="../frontend/pages/librarian/manage_extensions.php"><i class="fas fa-clock"></i> Manage Extensions</a>';
                echo '<div class="dropdown">';
                echo '<div class="dropdown-toggle">';
                echo '<i class="fas fa-money-bill-wave"></i> Payment';
                echo '<i class="fas fa-chevron-down"></i>';
                echo '</div>';
                echo '<ul class="submenu">';
                echo '<li><a href="../frontend/pages/librarian/confirm_fine.php"><i class="fas fa-check-circle"></i> Confirm Fine</a></li>';
                echo '<li><a href="../frontend/pages/librarian/enforce_fine.php"><i class="fas fa-gavel"></i> Enforce Fine</a></li>';
                echo '</ul>';
                echo '</div>';
            } elseif ($role_name == 'Acquisition') {
                echo '<a href=""><i class="fas fa-book-open"></i> Manage Acquisitions</a>';
            }

            if ($pendingFeedbackCount > 0) {
                echo '<a href="../frontend/pages/librarian/feedback display.php"><i class="fas fa-comments"></i> View Feedback <span class="badge badge-danger float-right">' . $pendingFeedbackCount . '</span></a>';
            }
            ?>
            <a href="../frontend/pages/librarian/search_books.php"><i class="fas fa-search"></i> Search Book</a>

            <a href="../frontend/pages/librarian/notifications.php"><i class="fas fa-bell"></i> Notifications
            <?php if ($notifications_count > 0): ?>
                <span class="notification-badge"><?php echo $notifications_count; ?></span>
            <?php endif; ?>
            </a>
            <a href="../frontend/login.html"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="content">
            <h1>Pending Borrow Requests</h1>
            <table class="table">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>User Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($pendingResult->num_rows > 0) {
                        while ($row = $pendingResult->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['author']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['user_name']) . "</td>";
                            echo "<td>
                                      <form action='confirm borrow.php' method='post' style='display:inline;'>
                                          <input type='hidden' name='request_id' value='" . $row['request_id'] . "'>
                                          <input type='submit' class='confirm-button' value='Confirm'>
                                      </form>
                                      <form action='reject borrow.php' method='post' style='display:inline;'>
                                          <input type='hidden' name='request_id' value='" . $row['request_id'] . "'>
                                          <input type='submit' class='reject-button' value='Reject'>
                                      </form>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center'>No pending borrow requests.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
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

<?php
// Close the database connection
$conn->close();
?>