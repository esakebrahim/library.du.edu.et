<?php
session_start();
include_once '../../../backend-php/database.php'; 

if (!isset($_SESSION['teacher_id'])) {
    die("Access denied. You must log in as a Teacher.");
}

$user_id = $_SESSION['teacher_id'];

// Fetch unread notifications count
$notif_sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? and status='unread'";
$notif_stmt = $conn->prepare($notif_sql);
$notif_stmt->bind_param("i", $user_id);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();
$notif_row = $notif_result->fetch_assoc();
$notification_count = $notif_row['count'];

// Fetch the count of borrowed books
$borrowed_sql = "SELECT COUNT(*) as borrowed_count FROM borrow_requests WHERE user_id = ? AND status = 'borrow_accept'";
$borrowed_stmt = $conn->prepare($borrowed_sql);
$borrowed_stmt->bind_param("i", $user_id);
$borrowed_stmt->execute();
$borrowed_result = $borrowed_stmt->get_result();
$borrowed_row = $borrowed_result->fetch_assoc();
$borrowed_count = $borrowed_row['borrowed_count'];

// Fetch the count of reserved books
$reserved_sql = "SELECT COUNT(*) as reserved_count FROM reservation WHERE user_id = ? and status='active'";
$reserved_stmt = $conn->prepare($reserved_sql);
$reserved_stmt->bind_param("i", $user_id);
$reserved_stmt->execute();
$reserved_result = $reserved_stmt->get_result();
$reserved_row = $reserved_result->fetch_assoc();
$reserved_count = $reserved_row['reserved_count'];

// Fetch recently borrowed books
$recent_borrow_sql = "
    SELECT books.title, borrow_requests.request_date, borrow_requests.due_date 
    FROM borrow_requests
    JOIN books ON borrow_requests.book_id = books.id 
    WHERE borrow_requests.user_id = ?  and (borrow_requests.status='borrow_accept' or borrow_requests.status='return_reject' or  borrow_requests.status='return_pending')
    ORDER BY borrow_requests.request_date DESC 
    LIMIT 5";

$recent_borrow_stmt = $conn->prepare($recent_borrow_sql);
$recent_borrow_stmt->bind_param("i", $user_id);
$recent_borrow_stmt->execute();
$recent_borrow_result = $recent_borrow_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Library System</title>
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
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background-color: rgba(255, 255, 255, 0.3);
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

        .dashboard-header {
            background: var(--white);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            text-align: center;
            background-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--white);
        }

        .dashboard-header h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .dashboard-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-box {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .stat-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary);
        }

        .stat-box h3 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .stat-box p {
            color: var(--secondary);
            font-size: 1rem;
            font-weight: 500;
        }

        .recent-borrows {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .recent-borrows h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--dark);
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary);
        }

        .recent-borrows table {
            width: 100%;
            border-collapse: collapse;
        }

        .recent-borrows th {
            background: var(--primary);
            color: var(--white);
            font-weight: 500;
            text-align: left;
            padding: 1rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .recent-borrows td {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            color: var(--secondary);
        }

        .recent-borrows tr:last-child td {
            border-bottom: none;
        }

        .recent-borrows tr:hover td {
            background: rgba(79, 70, 229, 0.05);
        }

        .no-records {
            text-align: center;
            padding: 2rem;
            color: var(--secondary);
            font-style: italic;
        }

        /* Security Section Styles */
        .security-section {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .security-header {
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--primary);
        }

        .security-header h3 {
            font-size: 1.25rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .security-header p {
            color: var(--secondary);
            margin-top: 0.25rem;
            font-size: 0.9rem;
        }

        .security-content {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .security-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: linear-gradient(135deg, #f6f7ff 0%, #f0f3ff 100%);
            border-radius: 0.75rem;
            border: 1px solid rgba(79, 70, 229, 0.1);
        }

        .security-status {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .security-icon {
            font-size: 1.5rem;
            color: var(--primary);
            background: rgba(79, 70, 229, 0.1);
            padding: 0.75rem;
            border-radius: 50%;
        }

        .status-text h4 {
            color: var(--dark);
            font-size: 1rem;
            margin-bottom: 0.15rem;
        }

        .status-text p {
            color: var(--secondary);
            font-size: 0.9rem;
        }

        .setup-2fa-btn {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background: var(--primary);
            color: var(--white);
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .setup-2fa-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .security-benefits {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
        }

        .benefit-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem;
            background: var(--white);
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }

        .benefit-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .benefit-item i {
            color: var(--primary);
            font-size: 1rem;
        }

        .benefit-item span {
            color: var(--dark);
            font-weight: 500;
            font-size: 0.9rem;
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

            .dashboard-stats {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .dashboard-header {
                padding: 1.5rem;
            }

            .dashboard-header h2 {
                font-size: 1.5rem;
            }

            .stat-box h3 {
                font-size: 2rem;
            }

            .recent-borrows {
                overflow-x: auto;
            }

            .security-benefits {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
            
            .security-info {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .security-status {
                flex-direction: column;
                text-align: center;
            }
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
            .mobile-menu-toggle {
                display: block;
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
      

        <!-- Chat -->
    
    </div>

    <div class="container">
        <div class="dashboard-header">
            <h2>Welcome to Your Dashboard</h2>
            <p>Manage your library activities and track your books</p>
        </div>

        <div class="dashboard-stats">
            <div class="stat-box">
                <h3><?php echo $borrowed_count; ?></h3>
                <p>Currently Borrowed Books</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $reserved_count; ?></h3>
                <p>Active Reservations</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $notification_count; ?></h3>
                <p>New Notifications</p>
            </div>
        </div>

        <!-- New 2FA Security Section -->
        <div class="security-section">
            <div class="security-header">
                <h3><i class="fas fa-shield-alt"></i> Account Security</h3>
                <p>Protect your account with Two-Factor Authentication</p>
            </div>
            <div class="security-content">
                <div class="security-info">
                    <div class="security-status">
                        <i class="fas fa-lock security-icon"></i>
                        <div class="status-text">
                            <h4>Two-Factor Authentication</h4>
                            <p>Add an extra layer of security to your account</p>
                        </div>
                    </div>
                    <a href="../two_factor_settings.php" class="setup-2fa-btn">
                        Configure 2FA Settings
                    </a>
                </div>
                <div class="security-benefits">
                    <div class="benefit-item">
                        <i class="fas fa-envelope"></i>
                        <span>Email Authentication</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-qrcode"></i>
                        <span>Authenticator App</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Choose Your Method</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="recent-borrows">
            <h3>Recently Borrowed Books</h3>
            <table>
                <tr>
                    <th>Book Title</th>
                    <th>Borrow Date</th>
                    <th>Due Date</th>
                </tr>
                <?php
                if ($recent_borrow_result->num_rows > 0) {
                    while ($row = $recent_borrow_result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['title']}</td>
                                <td>{$row['request_date']}</td>
                                <td>{$row['due_date']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' class='no-records'>No recent borrowed books</td></tr>";
                }
                ?>
            </table>
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
