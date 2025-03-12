<?php
session_start();
include '../../../backend-php/database.php';

if (!isset($_SESSION['librarian_id'])) {
    header("Location: ../../login.html");
    exit();
}

$librarianName = $_SESSION['librarian_name'];
$Id = $_SESSION['librarian_id'];



$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND status = 'unread'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $Id);
$stmt->execute();
$result = $stmt->get_result();
$notifications_count = $result->fetch_assoc()['unread_count'];
$stmt->close();


$pendingFeedbackCount = 0;
$sql = "SELECT COUNT(*) AS pending_count FROM feedback WHERE status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$pendingFeedbackCount = $row['pending_count']; 
$stmt->close();

$sql_role = "SELECT role_name FROM librarian_roles r JOIN users u ON r.id = u.role_id WHERE u.id = ?";
$stmt_role = $conn->prepare($sql_role);
$stmt_role->bind_param("i", $Id);
$stmt_role->execute();
$stmt_role->bind_result($role_name);
$stmt_role->fetch();
$stmt_role->close();

$sql_feedback = "SELECT id, student_id, feedback_text, status FROM feedback WHERE status = 'pending' ORDER BY id DESC";
$result_feedback = $conn->query($sql_feedback);
$sql_books = "SELECT COUNT(*) AS total_books FROM books";
$result_books = $conn->query($sql_books);
$totalBooks = $result_books->fetch_assoc()['total_books'] ?? 0;

$sql_borrow_pending = "SELECT COUNT(*) AS pending_borrow FROM borrow_requests WHERE status = 'borrow_pending'";
$pendingBorrow = $conn->query($sql_borrow_pending)->fetch_assoc()['pending_borrow'] ?? 0;

$sql_return_pending = "SELECT COUNT(*) AS pending_return FROM borrow_requests WHERE status = 'return_pending'";
$pendingReturn = $conn->query($sql_return_pending)->fetch_assoc()['pending_return'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #64748b;
            --success: #059669;
            --danger: #dc2626;
            --warning: #fbbf24;
            --white: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-800: #1e293b;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--gray-100);
        }

        .sidebar {
            min-height: 100vh;
            background: var(--gray-800);
            color: var(--white);
            padding: 1.5rem;
            width: 250px;
        }

        .sidebar h2 {
            font-size: 1.25rem;
            font-weight: 600;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar a {
            color: var(--gray-200);
            text-decoration: none;
            padding: 0.75rem 1rem;
            display: block;
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
            transition: all 0.2s;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
            color: var(--white);
            transform: translateX(4px);
        }

        .sidebar a i {
            width: 1.5rem;
            margin-right: 0.75rem;
        }

        .notification-badge {
            background: var(--danger);
            color: var(--white);
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            float: right;
        }

        .content {
            flex: 1;
            padding: 2rem;
            background: var(--gray-100);
        }

        .content h1 {
            font-size: 1.875rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 2rem;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 0.75rem;
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
        }

        .card h3 {
            color: var(--secondary);
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .card p {
            color: var(--gray-800);
            font-size: 1.875rem;
            font-weight: 600;
            margin: 0;
        }

        table {
            width: 100%;
            background: var(--white);
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        th {
            background: var(--gray-800);
            color: var(--white);
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1rem;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-100);
            color: var(--gray-800);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: var(--gray-50);
        }

        .badge {
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .dropdown .submenu {
            display: none;
            padding-left: 2rem;
        }

        .dropdown.active .submenu {
            display: block;
        }

        .dropdown-toggle {
            cursor: pointer;
        }

        .dropdown-toggle i.fa-chevron-down {
            transition: transform 0.2s;
            float: right;
            margin-top: 4px;
        }

        .dropdown.active .dropdown-toggle i.fa-chevron-down {
            transform: rotate(180deg);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                min-height: auto;
            }
            .content {
                padding: 1rem;
            }
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
        }

        /* Add new security card styles */
        .security-card {
            padding: 1.25rem !important;
        }

        .security-icon {
            width: 48px;
            height: 48px;
            background: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .security-icon i {
            font-size: 1.5rem;
            color: var(--white);
        }

        .security-info h3 {
            font-size: 1.1rem !important;
            color: var(--gray-800) !important;
            text-transform: none !important;
            letter-spacing: normal !important;
            margin-bottom: 0.25rem !important;
        }

        .security-info p {
            font-size: 0.875rem !important;
            color: var(--secondary) !important;
            font-weight: normal !important;
        }

        .auth-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 0.75rem;
            background: var(--gray-100);
            border-radius: 8px;
            font-size: 0.875rem;
            color: var(--gray-800);
            font-weight: 500;
        }

        .auth-badge i {
            margin-right: 0.375rem;
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .security-card .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .security-methods {
                margin-top: 1rem;
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                width: 100%;
            }

            .auth-badge {
                margin: 0.25rem 0;
                width: 100%;
                justify-content: center;
            }

            .security-methods .btn {
                margin: 0.5rem 0 0 0 !important;
                width: 100%;
            }
        }
    </style>
</head>
<body>
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
                echo '<a href="manage_extensions.php"><i class="fas fa-arrow-right"></i>Manage extension</a>';

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
            <a href="../librarian/search_books.php"><i class="fas fa-search"></i> Search Book</a>

            <a href="notifications.php"><i class="fas fa-bell"></i> Notifications
            <?php if ($notifications_count > 0): ?>
                <span class="notification-badge"><?php echo $notifications_count; ?></span>
            <?php endif; ?>
            </a>

            <a href="../../login.html"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="content">
            <h1>Welcome, <?php echo htmlspecialchars($librarianName); ?></h1>
            <div class="dashboard-cards">
                <div class="card"><h3>Total Books</h3><p><?php echo $totalBooks; ?></p></div>
                <div class="card"><h3>Pending Borrow</h3><p><?php echo $pendingBorrow; ?></p></div>
                <div class="card"><h3>Pending Return</h3><p><?php echo $pendingReturn; ?></p></div>
            </div>

            <!-- Security Card -->
            <div class="card security-card mb-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="security-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="security-info ml-3">
                            <h3 class="mb-1">Two-Factor Authentication</h3>
                            <p class="text-secondary mb-0">Choose your preferred authentication method</p>
                        </div>
                    </div>
                    <div class="security-methods">
                        <span class="auth-badge">
                            <i class="fas fa-envelope"></i> Email
                        </span>
                        <span class="auth-badge ml-2">
                            <i class="fas fa-qrcode"></i> Auth App
                        </span>
                        <a href="../two_factor_settings.php" class="btn btn-primary btn-sm ml-3">Configure 2FA</a>
                    </div>
                </div>
            </div>

            <!-- Feedback Table -->
            <?php if ($pendingFeedbackCount > 0) { ?>
                <h2>Pending Feedback</h2>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>Message</th>
                        <th>Status</th>
                    </tr>
                    <?php while ($feedback = $result_feedback->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $feedback['id']; ?></td>
                            <td><?php echo $feedback['student_id']; ?></td>
                            <td><?php echo htmlspecialchars($feedback['feedback_text']); ?></td>
                            <td><?php echo ucfirst($feedback['status']); ?></td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } else { ?>
                <p>No pending feedback at the moment.</p>
            <?php } ?>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let dropdowns = document.querySelectorAll(".dropdown");
            dropdowns.forEach(dropdown => {
                let toggle = dropdown.querySelector(".dropdown-toggle");
                toggle.addEventListener("click", function(event) {
                    event.preventDefault();
                    dropdown.classList.toggle("active");
                });
            });
        });
    </script>
</body>
</html>
