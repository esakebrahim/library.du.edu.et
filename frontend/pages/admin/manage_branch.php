<?php
session_start();
require_once '../../../backend-php/database.php';

// Check if user is logged in and is an admin


// Get admin details
$admin_id = $_SESSION['user_id'];
$admin_name = $_SESSION['user_name'];

// Handle branch operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();
        
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $name = sanitize_input($_POST['name']);
                    $address = sanitize_input($_POST['address']);
                    $phone = sanitize_input($_POST['phone']);
                    $email = sanitize_input($_POST['email']);
                    $capacity = (int)$_POST['capacity'];
                    
                    $stmt = $conn->prepare("
                        INSERT INTO library_branches (name, address, phone, email, capacity, created_at)
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->bind_param('ssssi', $name, $address, $phone, $email, $capacity);
                    $stmt->execute();
                    
                    $_SESSION['success'] = "Branch added successfully.";
                    break;
                    
                case 'update':
                    $id = (int)$_POST['id'];
                    $name = sanitize_input($_POST['name']);
                    $address = sanitize_input($_POST['address']);
                    $phone = sanitize_input($_POST['phone']);
                    $email = sanitize_input($_POST['email']);
                    $capacity = (int)$_POST['capacity'];
                    $status = sanitize_input($_POST['status']);
                    
                    $stmt = $conn->prepare("
                        UPDATE library_branches 
                        SET name = ?, address = ?, phone = ?, email = ?, capacity = ?, status = ?
                        WHERE id = ?
                    ");
                    $stmt->bind_param('ssssisi', $name, $address, $phone, $email, $capacity, $status, $id);
                    $stmt->execute();
                    
                    $_SESSION['success'] = "Branch updated successfully.";
                    break;
                    
                case 'delete':
                    $id = (int)$_POST['id'];
                    
                    // Check if branch has active librarians or books
                    $stmt = $conn->prepare("
                        SELECT 
                            (SELECT COUNT(*) FROM branch_librarians WHERE branch_id = ?) as librarian_count,
                            (SELECT COUNT(*) FROM books WHERE branch_id = ?) as book_count
                    ");
                    $stmt->bind_param('ii', $id, $id);
                    $stmt->execute();
                    $result = $stmt->get_result()->fetch_assoc();
                    
                    if ($result['librarian_count'] > 0 || $result['book_count'] > 0) {
                        throw new Exception("Cannot delete branch with active librarians or books.");
                    }
                    
                    $stmt = $conn->prepare("DELETE FROM library_branches WHERE id = ?");
                    $stmt->bind_param('i', $id);
                    $stmt->execute();
                    
                    $_SESSION['success'] = "Branch deleted successfully.";
                    break;
            }
        }
        
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    
    header("Location: manage_branch.php");
    exit();
}

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 10;
$offset = ($page - 1) * $limit;

// Get search and filter parameters
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$sort_by = isset($_GET['sort_by']) ? sanitize_input($_GET['sort_by']) : 'name';
$sort_order = isset($_GET['sort_order']) ? sanitize_input($_GET['sort_order']) : 'ASC';

// Validate sort parameters
$allowed_sort_fields = ['name', 'address', 'capacity', 'status', 'created_at'];
if (!in_array($sort_by, $allowed_sort_fields)) {
    $sort_by = 'name';
}
if (!in_array(strtoupper($sort_order), ['ASC', 'DESC'])) {
    $sort_order = 'ASC';
}

// Build query conditions
$conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $conditions[] = "(name LIKE ? OR address LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if (!empty($status)) {
    $conditions[] = "status = ?";
    $params[] = $status;
    $types .= 's';
}

// Build the WHERE clause
$where_clause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Get total count for pagination
$count_query = "
    SELECT COUNT(*) as total 
    FROM library_branches
    $where_clause
";
$stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_count = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_count / $limit);

// Get branches with pagination and additional details
$query = "
    SELECT 
        b.*,
        COUNT(DISTINCT bl.librarian_id) as librarian_count,
        COUNT(DISTINCT bk.id) as book_count
    FROM library_branches b
    LEFT JOIN branch_librarians bl ON b.id = bl.branch_id
    LEFT JOIN books bk ON b.id = bk.branch_id
    $where_clause
    GROUP BY b.id
    ORDER BY b.$sort_by $sort_order
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($query);

// Add pagination parameters
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Get branch statuses for filters
$statuses_query = "SELECT DISTINCT status FROM library_branches ORDER BY status";
$statuses_result = $conn->query($statuses_query);
$branch_statuses = [];
while ($row = $statuses_result->fetch_assoc()) {
    $branch_statuses[] = $row['status'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch Management - Library System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
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
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            color: white !important;
            font-weight: bold;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .table th {
            border-top: none;
            background-color: #f8f9fa;
            cursor: pointer;
        }
        
        .table th:hover {
            background-color: #e9ecef;
        }
        
        .btn-action {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .search-box {
            max-width: 300px;
        }
        
        .filter-section {
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .sidebar {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            height: 100%;
        }
        
        .nav-link {
            color: var(--dark-color);
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: var(--light-color);
            color: var(--secondary-color);
        }
        
        .nav-link i {
            margin-right: 10px;
        }
        
        .branch-info {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .modal-content {
            border-radius: 10px;
        }
        
        .form-label {
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">Library System Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($admin_name); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="admin_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../../login.html"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="sidebar">
                    <h5 class="mb-4">Admin Menu</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="admin_dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="bi bi-people"></i> User Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="manage_branch.php">
                                <i class="bi bi-building"></i> Branch Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="assign_librarian.html">
                                <i class="bi bi-person-plus"></i> Assign Librarians
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="action_log.php">
                                <i class="bi bi-activity"></i> Activity Log
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="generate_report.php">
                                <i class="bi bi-file-earmark-text"></i> Generate Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Branch Management</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBranchModal">
                            <i class="bi bi-plus-lg"></i> Add Branch
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="filter-section">
                            <form method="GET" class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-select">
                                        <option value="">All Statuses</option>
                                        <?php foreach ($branch_statuses as $s): ?>
                                            <option value="<?php echo $s; ?>" <?php echo $status === $s ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($s); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="search" class="form-label">Search</label>
                                    <input type="text" name="search" id="search" class="form-control search-box" 
                                           value="<?php echo htmlspecialchars($search); ?>" 
                                           placeholder="Search by name, address, or email">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                                </div>
                            </form>
                        </div>

                        <!-- Branches Table -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th onclick="sortTable('name')">Name <i class="bi bi-sort"></i></th>
                                        <th onclick="sortTable('address')">Address <i class="bi bi-sort"></i></th>
                                        <th>Contact</th>
                                        <th onclick="sortTable('capacity')">Capacity <i class="bi bi-sort"></i></th>
                                        <th>Staff/Books</th>
                                        <th onclick="sortTable('status')">Status <i class="bi bi-sort"></i></th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($branch = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div class="branch-info" title="<?php echo htmlspecialchars($branch['name']); ?>">
                                                    <?php echo htmlspecialchars($branch['name']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="branch-info" title="<?php echo htmlspecialchars($branch['address']); ?>">
                                                    <?php echo htmlspecialchars($branch['address']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="branch-info" title="<?php echo htmlspecialchars($branch['phone']); ?>">
                                                    <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($branch['phone']); ?>
                                                </div>
                                                <div class="branch-info" title="<?php echo htmlspecialchars($branch['email']); ?>">
                                                    <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($branch['email']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo $branch['capacity']; ?></td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $branch['librarian_count']; ?> Staff</span>
                                                <span class="badge bg-info"><?php echo $branch['book_count']; ?> Books</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $branch['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($branch['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-action" 
                                                        onclick="editBranch(<?php echo htmlspecialchars(json_encode($branch)); ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-action"
                                                        onclick="deleteBranch(<?php echo $branch['id']; ?>, '<?php echo htmlspecialchars($branch['name']); ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Branch Modal -->
    <div class="modal fade" id="addBranchModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Branch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="name" class="form-label">Branch Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="capacity" class="form-label">Capacity</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" min="1" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Branch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Branch Modal -->
    <div class="modal fade" id="editBranchModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Branch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Branch Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_address" class="form-label">Address</label>
                            <textarea class="form-control" id="edit_address" name="address" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="edit_phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_capacity" class="form-label">Capacity</label>
                            <input type="number" class="form-control" id="edit_capacity" name="capacity" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Branch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Branch Modal -->
    <div class="modal fade" id="deleteBranchModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Branch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <span id="delete_branch_name"></span>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Branch</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to sort table
        function sortTable(column) {
            const currentUrl = new URL(window.location.href);
            const currentOrder = currentUrl.searchParams.get('sort_order') || 'ASC';
            const currentSort = currentUrl.searchParams.get('sort_by') || 'name';
            
            let newOrder = 'ASC';
            if (currentSort === column) {
                newOrder = currentOrder === 'ASC' ? 'DESC' : 'ASC';
            }
            
            currentUrl.searchParams.set('sort_by', column);
            currentUrl.searchParams.set('sort_order', newOrder);
            window.location.href = currentUrl.toString();
        }

        // Function to edit branch
        function editBranch(branch) {
            document.getElementById('edit_id').value = branch.id;
            document.getElementById('edit_name').value = branch.name;
            document.getElementById('edit_address').value = branch.address;
            document.getElementById('edit_phone').value = branch.phone;
            document.getElementById('edit_email').value = branch.email;
            document.getElementById('edit_capacity').value = branch.capacity;
            document.getElementById('edit_status').value = branch.status;
            
            new bootstrap.Modal(document.getElementById('editBranchModal')).show();
        }

        // Function to delete branch
        function deleteBranch(id, name) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_branch_name').textContent = name;
            
            new bootstrap.Modal(document.getElementById('deleteBranchModal')).show();
        }

        // Show success/error messages
        <?php if (isset($_SESSION['success'])): ?>
            alert('<?php echo $_SESSION['success']; ?>');
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            alert('<?php echo $_SESSION['error']; ?>');
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>