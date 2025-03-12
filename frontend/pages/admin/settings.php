<?php
session_start();
require_once '../../../backend-php/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_name'])) {
    header("Location: ../../login.html");
    exit();
}

// Get admin details
$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];

// Get pending users count for the sidebar badge
$sql = "SELECT COUNT(*) AS pending_users FROM users WHERE status = 'pending'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$pendingUsersCount = $row['pending_users'];

// Handle user approval/rejection
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
    $action = $_GET['action'];

    if ($action === 'approve' || $action === 'reject') {
        $new_status = ($action === 'approve') ? 'approved' : 'rejected';
        
        $update_query = "UPDATE users SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $new_status, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "User has been " . $new_status . " successfully.";
        } else {
            $_SESSION['message'] = "Error updating user status: " . $conn->error;
        }
        
        $stmt->close();
        header("Location: settings.php");
        exit();
    }
}

// Fetch pending users
$query = "SELECT id, name, email, type, created_at FROM users WHERE status = 'pending' ORDER BY created_at DESC";
$result = $conn->query($query);

// Get message from session if exists
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Users - Library System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f1c40f;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-color);
            color: var(--dark-color);
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 1.5rem;
            transition: all 0.3s ease;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
        }

        .sidebar .navbar-brand {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            padding: 1rem 0;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background: white;
            color: var(--primary-color);
            font-weight: 600;
        }

        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 280px;
            flex-grow: 1;
            padding: 2rem;
            background: var(--light-color);
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1.5rem;
            border-radius: 15px 15px 0 0 !important;
        }

        .card-header h5 {
            color: var(--dark-color);
            font-weight: 600;
            margin: 0;
        }

        /* Table Styles */
        .table {
            margin-bottom: 0;
        }

        .table th {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
            font-weight: 600;
            border-bottom: none;
        }

        .table td {
            vertical-align: middle;
        }

        .badge {
            padding: 0.5rem 0.8rem;
            font-weight: 500;
            border-radius: 8px;
        }

        .btn-action {
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-size: 0.9rem;
            margin: 0 0.2rem;
        }

        .btn-approve {
            background-color: var(--success-color);
            border-color: var(--success-color);
            color: white;
        }

        .btn-reject {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                padding: 1rem 0.5rem;
            }

            .sidebar .navbar-brand {
                font-size: 1.2rem;
                padding: 0.5rem;
            }

            .sidebar .nav-link span {
                display: none;
            }

            .main-content {
                margin-left: 80px;
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
            <a class="nav-link active" href="settings.php">
                <i class="fas fa-user-check"></i>
                <span>Approve Users</span>
                <?php if ($pendingUsersCount > 0): ?>
                    <span class="badge bg-danger"><?php echo $pendingUsersCount; ?></span>
                <?php endif; ?>
            </a>
            <a class="nav-link" href="view_users.php"><i class="fas fa-users"></i><span>View Users</span></a>
            <a class="nav-link" href="add_user.php"><i class="fas fa-user-plus"></i><span>Add User</span></a>
               
            <a class="nav-link" href="view_branch.php"><i class="fas fa-building"></i><span>View Branches</span></a>
            <a class="nav-link" href="add_branch.php"><i class="fas fa-plus-circle"></i><span>Add Branch</span></a>
           
            <a class="nav-link" href="assign_librarian.html"><i class="fas fa-user-tie"></i><span>Assign Librarian</span></a>
            <a class="nav-link" href="admin_dashboard.php"><i class="fas fa-user-shield"></i><span>Assign Role</span></a>
            <a class="nav-link" href="action_log.php"><i class="fas fa-history"></i><span>Librarian Actions</span></a>
            <a class="nav-link" href="../../login.html"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Pending User Approvals</h5>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="pendingUsersTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Registration Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo ucfirst(htmlspecialchars($row['type'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <a href="?action=approve&user_id=<?php echo $row['id']; ?>" 
                                               class="btn btn-action btn-approve" 
                                               onclick="return confirm('Are you sure you want to approve this user?')">
                                                <i class="fas fa-check"></i> Approve
                                            </a>
                                            <a href="?action=reject&user_id=<?php echo $row['id']; ?>" 
                                               class="btn btn-action btn-reject" 
                                               onclick="return confirm('Are you sure you want to reject this user? This action cannot be undone.')">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        No pending users to approve at this time.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#pendingUsersTable').DataTable({
                order: [[3, 'desc']], // Sort by registration date by default
                pageLength: 10,
                language: {
                    search: "Search pending users:"
                }
            });

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

            // Auto-close alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>