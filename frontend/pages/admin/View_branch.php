<?php
session_start();
// Include the database connection file
require_once '../../../backend-php/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../frontend/login.html");
    exit();
}

$adminName = $_SESSION['admin_name'];

// Get pending users count for the sidebar badge
$sql = "SELECT COUNT(*) AS pending_users FROM users WHERE status = 'pending'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$pendingUsersCount = $row['pending_users'];

// Fetch all library branches along with their respective campuses
$query = "SELECT lb.id, lb.name AS branch_name, c.name AS campus_name 
          FROM library_branches lb 
          JOIN campuses c ON lb.campus_id = c.id";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Library Branches</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --light-bg: #f8f9fa;
            --dark-text: #2b2d42;
            --light-text: #8d99ae;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light-bg);
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
            background: var(--light-bg);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            border-radius: 15px 15px 0 0 !important;
        }

        .card-header h5 {
            color: var(--dark-text);
            font-weight: 600;
            margin: 0;
        }

        .table {
            margin: 0;
        }

        .table th {
            background: var(--primary-color);
            color: white;
            font-weight: 500;
            border: none;
        }

        .table td {
            vertical-align: middle;
            color: var(--dark-text);
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
                <i class="fas fa-user-check"s></i>
                <span>Approve Users</span>
                <?php if ($pendingUsersCount > 0): ?>
                    <span class="badge badge-danger"><?php echo $pendingUsersCount; ?></span>
                <?php endif; ?>
            </a>
            <a class="nav-link" href="view_users.php"><i class="fas fa-users"></i><span>View Users</span></a>
            <a class="nav-link" href="add_user.php"><i class="fas fa-user-plus"></i><span>Add User</span></a>
          
            <a class="nav-link active" href="view_branch.php"><i class="fas fa-building"></i><span>View Branches</span></a>
            <a class="nav-link" href="add_branch.php"><i class="fas fa-plus-circle"></i><span>Add Branch</span></a>
         
            <a class="nav-link" href="assign_librarian.html"><i class="fas fa-user-tie"></i><span>Assign Librarian</span></a>
            <a class="nav-link" href="admin_dashboard.php"><i class="fas fa-user-shield"></i><span>Assign Role</span></a>
            <a class="nav-link" href="action_log.php"><i class="fas fa-history"></i><span>Librarian Actions</span></a>
            <a class="nav-link" href="../frontend/login.html"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="card">
            <div class="card-header">
                <h5>Library Branches</h5>
            </div>
            <div class="card-body">
                <?php if ($result->num_rows > 0): ?>
                    <table class="table table-hover" id="branchesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Branch Name</th>
                                <th>Campus Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['branch_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['campus_name']); ?></td>
                                    <td>
                                        <a href="edit_branch.php?id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="deleteBranch(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['branch_name']); ?>')">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-warning" role="alert">
                        No branches found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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

        function deleteBranch(branchId, branchName) {
            if (confirm('Are you sure you want to delete the branch "' + branchName + '"? This action cannot be undone.')) {
                window.location.href = '../../../backend-php/delete_branch.php?id=' + branchId;
            }
        }

        $(document).ready(function() {
            // Initialize DataTable
            $('#branchesTable').DataTable({
                order: [[0, 'asc']], // Sort by ID by default
                pageLength: 10,
                language: {
                    search: "Search branches:"
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