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

// Initialize message variable
$message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $branch_name = trim($_POST['branch_name']);
    $campus_id = (int)$_POST['campus_id'];

    // Validate input
    if (empty($branch_name) || empty($campus_id)) {
        $message = '<div class="alert alert-danger">Please fill in all required fields.</div>';
    } else {
        // Check if branch already exists
        $check_query = "SELECT id FROM library_branches WHERE name = ? AND campus_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("si", $branch_name, $campus_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = '<div class="alert alert-warning">A branch with this name already exists in the selected campus.</div>';
        } else {
            // Insert new branch
            $insert_query = "INSERT INTO library_branches (name, campus_id) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("si", $branch_name, $campus_id);

            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Library branch added successfully!</div>';
                // Clear form data
                $branch_name = '';
                $campus_id = '';
            } else {
                $message = '<div class="alert alert-danger">Error adding library branch: ' . $conn->error . '</div>';
            }
        }
        $stmt->close();
    }
}

// Fetch campuses for dropdown
$campus_query = "SELECT id, name FROM campuses ORDER BY name";
$campus_result = $conn->query($campus_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Branch - Library System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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

        .form-control {
            border-radius: 10px;
            border: 1px solid rgba(0,0,0,0.1);
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            border-color: var(--secondary-color);
        }

        .btn-primary {
            background: var(--secondary-color);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: var(--primary-color);
            transform: translateY(-1px);
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
            <a class="nav-link" href="view_users.php"><i class="fas fa-users"></i><span>View Users</span></a>
            <a class="nav-link" href="add_user.php"><i class="fas fa-user-plus"></i><span>Add User</span></a>
         
            <a class="nav-link" href="view_branch.php"><i class="fas fa-building"></i><span>View Branches</span></a>
            <a class="nav-link active" href="add_branch.php"><i class="fas fa-plus-circle"></i><span>Add Branch</span></a>
           
            <a class="nav-link" href="assign_librarian.html"><i class="fas fa-user-tie"></i><span>Assign Librarian</span></a>
            <a class="nav-link" href="admin_dashboard.php"><i class="fas fa-user-shield"></i><span>Assign Role</span></a>
            <a class="nav-link" href="action_log.php"><i class="fas fa-history"></i><span>Librarian Actions</span></a>
            <a class="nav-link" href="../../login.html"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="card">
            <div class="card-header">
                <h5>Add New Library Branch</h5>
            </div>
            <div class="card-body">
                <?php if ($message) echo $message; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group mb-3">
                        <label for="branch_name" class="form-label">Branch Name:</label>
                        <input type="text" class="form-control" id="branch_name" name="branch_name" 
                               value="<?php echo isset($branch_name) ? htmlspecialchars($branch_name) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group mb-4">
                        <label for="campus_id" class="form-label">Campus:</label>
                        <select class="form-control" id="campus_id" name="campus_id" required>
                            <option value="">Select Campus</option>
                            <?php while ($campus = $campus_result->fetch_assoc()): ?>
                                <option value="<?php echo $campus['id']; ?>" 
                                    <?php echo (isset($campus_id) && $campus_id == $campus['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($campus['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Add Branch</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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