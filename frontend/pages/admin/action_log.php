<?php
session_start();
include '../../../backend-php/database.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../login.html");
    exit();
}

// Get pending users count for the sidebar badge
$sql = "SELECT COUNT(*) AS pending_users FROM users WHERE status = 'pending'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$pendingUsersCount = $row['pending_users'];

// Fetch librarian actions
$librarianActionsQuery = "
    SELECT la.id, la.librarian_id, la.action, la.timestamp, u.name AS librarian_name, u.last_name AS librarian_last_name
    FROM librarian_actions_log la
    JOIN users u ON la.librarian_id = u.id
    ORDER BY la.timestamp DESC";
$librarianActionsResult = $conn->query($librarianActionsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Actions - Library System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --secondary: #64748b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --background: #f8fafc;
            --surface: #ffffff;
            --text: #0f172a;
            --text-light: #64748b;
            --border: #e2e8f0;
        }

        body {
            background-color: var(--background);
            font-family: 'Inter', sans-serif;
            display: flex;
            min-height: 100vh;
            margin: 0;
            color: var(--text);
        }

        /* Sidebar Styles */
         /* Sidebar Styles */
         .sidebar {
            width: 280px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 1.5rem;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar .navbar-brand {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            padding: 1rem 0;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1rem;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.8rem 1rem;
            margin: 0.2rem 0;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background: white;
            color: var(--primary);
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }
           

        /* Main Content Styles */
        .main-content {
            margin-left: 280px;
            flex-grow: 1;
            padding: 2rem;
            background: var(--background);
        }

        .card {
            border: none;
            border-radius: 24px;
            background: var(--surface);
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 
                        0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 
                        0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 1.5rem 2rem;
            border-radius: 24px 24px 0 0 !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-header h5 {
            margin: 0;
            font-weight: 600;
            color: var(--text);
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-header h5 i {
            color: var(--primary);
            font-size: 1.5rem;
        }

        .card-body {
            padding: 1.5rem 2rem;
        }

        /* Table Styles */
        .table-container {
            background: var(--surface);
            border-radius: 16px;
            overflow: hidden;
        }

        .table {
            margin: 0;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table th {
            background: #f8fafc;
            color: var(--text-light);
            font-weight: 600;
            padding: 1.25rem 1.5rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid var(--border);
        }

        .table td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            color: var(--text);
            border-bottom: 1px solid var(--border);
            font-size: 0.95rem;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table-hover tbody tr:hover {
            background: #f1f5f9;
            transform: scale(1.01);
        }

        .timestamp {
            font-size: 0.875rem;
            color: var(--text-light);
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .timestamp i {
            color: var(--text-light);
            font-size: 0.875rem;
        }

        .action-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            background: #f0f9ff;
            color: var(--primary);
            transition: all 0.2s ease;
        }

        .action-badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(59, 130, 246, 0.1);
        }

        .table td:first-child {
            font-weight: 600;
            color: var(--primary);
            font-size: 0.875rem;
        }

        .librarian-name {
            font-weight: 500;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .librarian-name i {
            color: var(--text-light);
            font-size: 0.875rem;
        }

        .empty-state {
            padding: 3rem 0;
            text-align: center;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--text-light);
        }

        .empty-state p {
            margin: 0;
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .card-header, .card-body {
                padding: 1.25rem;
            }

            .table th, .table td {
                padding: 1rem;
            }

            .action-badge {
                padding: 0.375rem 0.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="navbar-brand">📚 LMS</div>
        <div class="navbar-nav">
            <a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a class="nav-link" href="settings.php">
                <i class="fas fa-user-check"></i>
                <span>Approve Users</span>
                <?php if ($pendingUsersCount > 0): ?>
                    <span class="badge badge-danger"><?php echo $pendingUsersCount; ?></span>
                <?php endif; ?>
            </a>
            <a class="nav-link" href="view_users.php"><i class="fas fa-users"></i><span>View Users</span></a>
            <a class="nav-link" href="add_user.php"><i class="fas fa-user-plus"></i><span>Add User</span></a>
            
            <a class="nav-link" href="view_branch.php"><i class="fas fa-building"></i><span>View Branches</span></a>
            <a class="nav-link" href="add_branch.php"><i class="fas fa-plus-circle"></i><span>Add Branch</span></a>
            
            <a class="nav-link" href="assign_librarian.html"><i class="fas fa-user-tie"></i><span>Assign Librarian</span></a>
            <a class="nav-link" href="admin_dashboard.php"><i class="fas fa-user-shield"></i><span>Assign Role</span></a>
            <a class="nav-link active" href="action_log.php"><i class="fas fa-history"></i><span>Librarian Actions</span></a>
            <a class="nav-link" href="../../login.html"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="card">
            <div class="card-header">
                <h5>
                    <i class="fas fa-history"></i>
                    Librarian Activity Log
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <div class="table-container">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Librarian Name</th>
                                    <th>Action Type</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($librarianActionsResult->num_rows > 0): ?>
                                    <?php while ($row = $librarianActionsResult->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?= htmlspecialchars($row['id']) ?></td>
                                            <td>
                                                <div class="librarian-name">
                                                    <i class="fas fa-user"></i>
                                                    <?= htmlspecialchars($row['librarian_name'] . " " . $row['librarian_last_name']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="action-badge">
                                                    <i class="fas fa-circle-check"></i>
                                                    <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $row['action']))) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="timestamp">
                                                    <i class="fas fa-clock"></i>
                                                    <?= htmlspecialchars(date('M d, Y h:i A', strtotime($row['timestamp']))) ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4">
                                            <div class="empty-state">
                                                <i class="fas fa-inbox"></i>
                                                <p>No librarian actions recorded yet.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add hover effect to sidebar links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('mouseenter', function() {
                if (!this.classList.contains('active')) {
                    this.style.transform = 'translateX(5px)';
                }
            });
            link.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
            });
        });
    </script>
</body>
</html>
<?php
// Close the database connection
$conn->close();
?>