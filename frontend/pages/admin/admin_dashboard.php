<?php
// Include database connection
include_once '../../../backend-php/database.php';

session_start();
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

// Get list of all librarians
$librarians_query = "SELECT id, name FROM users WHERE type = 'librarian'";
$librarians_result = mysqli_query($conn, $librarians_query);

// Get list of roles
$roles_query = "SELECT id, role_name FROM librarian_roles";
$roles_result = mysqli_query($conn, $roles_query);

// Get assigned roles
$assigned_roles_query = "
    SELECT users.id AS librarian_id, users.name AS librarian_name, librarian_roles.role_name 
    FROM users 
    JOIN librarian_roles ON users.role_id = librarian_roles.id 
    WHERE users.type = 'librarian'";
$assigned_roles_result = mysqli_query($conn, $assigned_roles_query);

// Handle unassign role request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unassign_id'])) {
    $unassign_id = $_POST['unassign_id'];
    $unassign_query = "UPDATE users SET role_id = NULL WHERE id = ?";
    $stmt = $conn->prepare($unassign_query);
    $stmt->bind_param("i", $unassign_id);
    $stmt->execute();
    header("Location: " . $_SERVER['PHP_SELF']); // Refresh page
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Roles - Library System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --success: #059669;
            --danger: #dc2626;
            --warning: #d97706;
            --background: #f1f5f9;
            --surface: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
        }

        body {
            background-color: var(--background);
            font-family: 'Poppins', sans-serif;
            display: flex;
            min-height: 100vh;
            margin: 0;
            color: var(--text);
        }

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
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            background: var(--surface);
            margin-bottom: 2rem;
        }

        .card-header {
            background: var(--surface);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            border-radius: 16px 16px 0 0 !important;
        }

        .card-header h5 {
            margin: 0;
            font-weight: 600;
            color: var(--text);
            font-size: 1.25rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            font-weight: 500;
            color: var(--text);
            margin-bottom: 0.75rem;
            display: block;
            font-size: 1rem;
        }

        .form-control {
            border-radius: 12px;
            padding: 1rem 1.25rem;
            border: 1px solid #e2e8f0;
            font-size: 1rem;
            transition: all 0.3s ease;
            height: 3.5rem;
            line-height: 1.5;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1.25rem center;
            background-size: 16px 12px;
            padding-right: 3rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-danger {
            background: var(--danger);
            border: none;
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
            transform: translateY(-1px);
        }

        .table {
            margin: 0;
        }

        .table th {
            background: #f8fafc;
            color: var(--text);
            font-weight: 600;
            border: none;
            padding: 1rem;
            font-size: 0.95rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            color: var(--text);
            border-top: 1px solid #e2e8f0;
        }

        .table-hover tbody tr:hover {
            background: #f8fafc;
        }

        .badge {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            border-radius: 6px;
        }

        .badge-danger {
            background: var(--danger);
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
            }

            .sidebar .nav-link span {
                display: none;
            }

            .main-content {
                margin-left: 80px;
            }

            .sidebar .navbar-brand {
                font-size: 1.2rem;
                padding: 0.5rem;
            }
        }
        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: fadeIn 0.3s ease;
        }

        .message.success {
            background: rgba(34, 197, 94, 0.1);
            color: var(--success);
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .message.error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error);
            border: 1px solid rgba(239, 68, 68, 0.2);
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
            <a class="nav-link active" href="admin_dashboard.php"><i class="fas fa-user-shield"></i><span>Assign Role</span></a>
            <a class="nav-link" href="action_log.php"><i class="fas fa-history"></i><span>Librarian Actions</span></a>
            <a class="nav-link" href="../../login.html"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
    </div>

  <!-- Main Content -->
  <main class="main-content">
        <!-- Assign Role Form -->
        <div class="glass-card">
            <div class="card-header">
                <h5><i class="fas fa-user-tag me-2"></i>Assign Role to Librarian</h5>
            </div>
            <div class="card-body">
                <div id="messageContainer" class="message" style="display: none;"></div>
                <form id="assignRoleForm" action="assign_role.php" method="POST">
                    <div class="form-group">
                        <label for="librarian_id">Select Librarian</label>
                        <select class="form-control" id="librarian_id" name="librarian_id" required>
                            <option value="">Choose a librarian...</option>
                            <?php while($librarian = mysqli_fetch_assoc($librarians_result)): ?>
                                <option value="<?php echo htmlspecialchars($librarian['id']); ?>">
                                    <?php echo htmlspecialchars($librarian['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="role_id">Select Role</label>
                        <select class="form-control" id="role_id" name="role_id" required>
                            <option value="">Choose a role...</option>
                            <?php while($role = mysqli_fetch_assoc($roles_result)): ?>
                                <option value="<?php echo htmlspecialchars($role['id']); ?>">
                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-check"></i>
                        Assign Role
                    </button>
                </form>
            </div>
        </div>

        <!-- Current Assignments -->
        <div class="glass-card">
            <div class="card-header">
                <h5><i class="fas fa-list-check me-2"></i>Current Role Assignments</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Librarian Name</th>
                                <th>Assigned Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($assignment = mysqli_fetch_assoc($assigned_roles_result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($assignment['librarian_name']); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['role_name']); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="unassign_id" value="<?php echo $assignment['librarian_id']; ?>">
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-user-minus"></i>
                                                Unassign
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <script>
        document.getElementById('assignRoleForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('assign_role.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                const messageContainer = document.getElementById('messageContainer');
                if (data.includes('successfully')) {
                    messageContainer.className = 'message success';
                    messageContainer.innerHTML = '<i class="fas fa-check-circle"></i> ' + data;
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    messageContainer.className = 'message error';
                    messageContainer.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data;
                }
                messageContainer.style.display = 'flex';
                setTimeout(() => {
                    messageContainer.style.display = 'none';
                }, 5000);
            })
            .catch(error => {
                const messageContainer = document.getElementById('messageContainer');
                messageContainer.className = 'message error';
                messageContainer.innerHTML = '<i class="fas fa-exclamation-circle"></i> An error occurred. Please try again.';
                messageContainer.style.display = 'flex';
            });
        });
    </script>
</body>
</html>
<?php
// Close the database connection
$conn->close();
?>