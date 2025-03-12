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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Extension Requests</title>
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
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
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
        }

        .container {
            max-width: 900px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .action-btn {
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
            border: none;
            transition: 0.3s;
        }

        .approve-btn {
            background-color: #28a745;
            color: white;
        }

        .approve-btn:hover {
            background-color: #218838;
        }

        .reject-btn {
            background-color: #dc3545;
            color: white;
        }

        .reject-btn:hover {
            background-color: #c82333;
        }

        .mobile-menu-toggle {
            display: none;
        }

        .dropdown {
            position: relative;
        }

        .dropdown-toggle {
            width: 100%;
        }

        .dropdown-toggle .fa-chevron-down {
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
            padding-left: 3rem;
            font-size: 0.9rem;
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
                echo '<a href="confirm return.php"><i class="fas fa-arrow-left"></i> Return Book</a>';
                echo '<a href="manage_extensions.php" class="active"><i class="fas fa-clock"></i> Manage Extensions</a>';
                echo '<div class="dropdown">';
                echo '<a href="#" class="dropdown-toggle"><i class="fas fa-money-bill-wave"></i> Payment <i class="fas fa-chevron-down float-right"></i></a>';
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
            <div class="container">
                <h2 class="text-center">Manage Extension Requests</h2>
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Teacher Name</th>
                            <th>Book Title</th>
                            <th>Due Date</th>
                            <th>Request Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch all pending extension requests
                        $sql = "SELECT extension_requests.id, users.name AS teacher_name, books.title, borrow_requests.due_date, extension_requests.request_date
                                FROM extension_requests
                                JOIN borrow_requests ON extension_requests.borrow_id = borrow_requests.id
                                JOIN users ON extension_requests.user_id = users.id
                                JOIN books ON borrow_requests.book_id = books.id
                                WHERE extension_requests.status = 'pending'";

                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                        <td>{$row['id']}</td>
                                        <td>{$row['teacher_name']}</td>
                                        <td>{$row['title']}</td>
                                        <td>{$row['due_date']}</td>
                                        <td>{$row['request_date']}</td>
                                        <td>
                                            <form method='POST' action='process_extension_decision.php' style='display:inline-block;'>
                                                <input type='hidden' name='extension_id' value='{$row['id']}'>
                                                <input type='hidden' name='decision' value='approved'>
                                                <input type='submit' class='action-btn approve-btn' value='Approve'>
                                            </form>
                                            <form method='POST' action='process_extension_decision.php' style='display:inline-block;'>
                                                <input type='hidden' name='extension_id' value='{$row['id']}'>
                                                <input type='hidden' name='decision' value='rejected'>
                                                <input type='submit' class='action-btn reject-btn' value='Reject'>
                                            </form>
                                        </td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>No pending extension requests</td></tr>";
                        }

                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
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
                dropdownToggle.addEventListener('click', (e) => {
                    e.preventDefault();
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
