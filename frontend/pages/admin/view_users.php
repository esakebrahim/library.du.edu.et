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

// Fetch all users with their roles
$query = "SELECT id, name, email, type, status, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users - Library System</title>
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
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            color: white !important;
            font-weight: bold;
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

        .badge-pending {
            background-color: var(--warning-color);
            color: #000;
        }

        .badge-approved {
            background-color: var(--success-color);
            color: white;
        }

        .badge-rejected {
            background-color: var(--danger-color);
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
            <a class="nav-link" href="settings.php">
                <i class="fas fa-user-check"></i>
                <span>Approve Users</span>
                <?php if ($pendingUsersCount > 0): ?>
                    <span class="badge bg-danger"><?php echo $pendingUsersCount; ?></span>
                <?php endif; ?>
            </a>
            <a class="nav-link active" href="view_users.php"><i class="fas fa-users"></i><span>View Users</span></a>
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
                <h5>User List</h5>
                <a href="add_user.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add New User
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="usersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo ucfirst(htmlspecialchars($row['type'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $row['status']; ?>">
                                            <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <a href="update user.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
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
            $('#usersTable').DataTable({
                order: [[5, 'desc']], // Sort by created_at by default
                pageLength: 10,
                language: {
                    search: "Search users:"
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
        });

        // Function to delete user
        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                window.location.href = `../../../backend-php/delete_user.php?id=${userId}`;
            }
        }
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>