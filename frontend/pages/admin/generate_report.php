<?php
session_start();
require_once '../../../backend-php/database.php';

// Check if user is logged in and is an admin
if (!is_logged_in() || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../../login.html");
    exit();
}

// Get admin details
$admin_id = $_SESSION['user_id'];
$admin_name = $_SESSION['name'];

// Handle report generation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_type = sanitize_input($_POST['report_type']);
    $date_from = sanitize_input($_POST['date_from']);
    $date_to = sanitize_input($_POST['date_to']);
    $branch_id = isset($_POST['branch_id']) ? (int)$_POST['branch_id'] : null;
    $format = sanitize_input($_POST['format']);

    // Validate dates
    if (empty($date_from) || empty($date_to) || strtotime($date_from) > strtotime($date_to)) {
        $_SESSION['error'] = "Invalid date range selected.";
    } else {
        // Generate report based on type
        switch ($report_type) {
            case 'borrowings':
                $report_data = generate_borrowings_report($conn, $date_from, $date_to, $branch_id);
                break;
            case 'users':
                $report_data = generate_users_report($conn, $branch_id);
                break;
            case 'books':
                $report_data = generate_books_report($conn, $branch_id);
                break;
            case 'feedback':
                $report_data = generate_feedback_report($conn, $date_from, $date_to, $branch_id);
                break;
            default:
                $_SESSION['error'] = "Invalid report type selected.";
                break;
        }

        if (isset($report_data)) {
            // Export report in selected format
            export_report($report_data, $report_type, $format);
            exit();
        }
    }
}

// Get branches for filter
$branches_query = "SELECT id, name FROM library_branches ORDER BY name";
$branches_result = $conn->query($branches_query);
$branches = [];
while ($row = $branches_result->fetch_assoc()) {
    $branches[] = $row;
}

$conn->close();

// Helper functions for report generation
function generate_borrowings_report($conn, $date_from, $date_to, $branch_id) {
    $query = "
        SELECT 
            b.id,
            b.title,
            u.name as borrower_name,
            u.type as borrower_type,
            br.branch_name,
            br.borrow_date,
            br.return_date,
            br.status
        FROM borrows br
        JOIN books b ON br.book_id = b.id
        JOIN users u ON br.user_id = u.id
        JOIN library_branches lb ON br.branch_id = lb.id
        WHERE br.borrow_date BETWEEN ? AND ?
    ";
    
    $params = [$date_from, $date_to];
    $types = 'ss';

    if ($branch_id) {
        $query .= " AND br.branch_id = ?";
        $params[] = $branch_id;
        $types .= 'i';
    }

    $query .= " ORDER BY br.borrow_date DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function generate_users_report($conn, $branch_id) {
    $query = "
        SELECT 
            u.id,
            u.name,
            u.email,
            u.type,
            u.status,
            u.created_at,
            COUNT(DISTINCT br.id) as total_borrows,
            COUNT(DISTINCT f.id) as total_feedback
        FROM users u
        LEFT JOIN borrows br ON u.id = br.user_id
        LEFT JOIN feedback f ON u.id = f.user_id
    ";

    if ($branch_id) {
        $query .= " WHERE br.branch_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $branch_id);
    } else {
        $stmt = $conn->prepare($query);
    }

    $query .= " GROUP BY u.id ORDER BY u.created_at DESC";
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function generate_books_report($conn, $branch_id) {
    $query = "
        SELECT 
            b.id,
            b.title,
            b.author,
            b.isbn,
            b.status,
            lb.name as branch_name,
            COUNT(DISTINCT br.id) as total_borrows,
            COUNT(DISTINCT f.id) as total_feedback
        FROM books b
        JOIN library_branches lb ON b.branch_id = lb.id
        LEFT JOIN borrows br ON b.id = br.book_id
        LEFT JOIN feedback f ON b.id = f.book_id
    ";

    if ($branch_id) {
        $query .= " WHERE b.branch_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $branch_id);
    } else {
        $stmt = $conn->prepare($query);
    }

    $query .= " GROUP BY b.id ORDER BY b.title";
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function generate_feedback_report($conn, $date_from, $date_to, $branch_id) {
    $query = "
        SELECT 
            f.id,
            f.feedback_text,
            f.rating,
            f.created_at,
            u.name as user_name,
            u.type as user_type,
            b.title as book_title,
            lb.name as branch_name
        FROM feedback f
        JOIN users u ON f.user_id = u.id
        JOIN books b ON f.book_id = b.id
        JOIN library_branches lb ON b.branch_id = lb.id
        WHERE f.created_at BETWEEN ? AND ?
    ";

    $params = [$date_from, $date_to];
    $types = 'ss';

    if ($branch_id) {
        $query .= " AND b.branch_id = ?";
        $params[] = $branch_id;
        $types .= 'i';
    }

    $query .= " ORDER BY f.created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function export_report($data, $report_type, $format) {
    $filename = $report_type . '_report_' . date('Y-m-d_His');
    
    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Write headers
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }
        
        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
    } else {
        // Generate PDF using TCPDF
        require_once('../../../backend-php/tcpdf/tcpdf.php');
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator('Library System');
        $pdf->SetAuthor('Admin');
        $pdf->SetTitle(ucfirst($report_type) . ' Report');
        
        $pdf->AddPage();
        
        // Add content
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, ucfirst($report_type) . ' Report', 0, 1, 'C');
        $pdf->Ln(10);
        
        $pdf->SetFont('helvetica', '', 10);
        
        // Add table headers
        if (!empty($data)) {
            $headers = array_keys($data[0]);
            $pdf->SetFillColor(240, 240, 240);
            $pdf->SetFont('helvetica', 'B', 10);
            
            foreach ($headers as $header) {
                $pdf->Cell(40, 7, ucfirst(str_replace('_', ' ', $header)), 1, 0, 'L', true);
            }
            $pdf->Ln();
            
            // Add data
            $pdf->SetFont('helvetica', '', 10);
            foreach ($data as $row) {
                foreach ($row as $value) {
                    $pdf->Cell(40, 6, $value, 1);
                }
                $pdf->Ln();
            }
        }
        
        $pdf->Output($filename . '.pdf', 'D');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports - Library System</title>
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
        
        .report-card {
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        
        .report-card:hover {
            transform: translateY(-5px);
        }
        
        .report-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--light-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        
        .filter-section {
            background-color: white;
            padding: 20px;
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
        
        .preview-section {
            display: none;
            margin-top: 20px;
        }
        
        .preview-table {
            width: 100%;
            overflow-x: auto;
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
                            <a class="nav-link" href="manage_branch.php">
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
                            <a class="nav-link active" href="generate_report.php">
                                <i class="bi bi-file-earmark-text"></i> Generate Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Generate Reports</h5>
                    </div>
                    <div class="card-body">
                        <!-- Report Type Selection -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card report-card" onclick="selectReportType('borrowings')">
                                    <div class="card-body text-center">
                                        <div class="report-icon bg-primary text-white">
                                            <i class="bi bi-book"></i>
                                        </div>
                                        <h6>Borrowings Report</h6>
                                        <p class="text-muted small">Track book borrowings and returns</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card report-card" onclick="selectReportType('users')">
                                    <div class="card-body text-center">
                                        <div class="report-icon bg-success text-white">
                                            <i class="bi bi-people"></i>
                                        </div>
                                        <h6>Users Report</h6>
                                        <p class="text-muted small">User statistics and activities</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card report-card" onclick="selectReportType('books')">
                                    <div class="card-body text-center">
                                        <div class="report-icon bg-info text-white">
                                            <i class="bi bi-collection"></i>
                                        </div>
                                        <h6>Books Report</h6>
                                        <p class="text-muted small">Book inventory and usage</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card report-card" onclick="selectReportType('feedback')">
                                    <div class="card-body text-center">
                                        <div class="report-icon bg-warning text-white">
                                            <i class="bi bi-chat-dots"></i>
                                        </div>
                                        <h6>Feedback Report</h6>
                                        <p class="text-muted small">User feedback and ratings</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Report Generation Form -->
                        <form method="POST" id="reportForm" class="filter-section">
                            <input type="hidden" name="report_type" id="report_type">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="branch_id" class="form-label">Branch</label>
                                    <select name="branch_id" id="branch_id" class="form-select">
                                        <option value="">All Branches</option>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?php echo $branch['id']; ?>">
                                                <?php echo htmlspecialchars($branch['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="format" class="form-label">Export Format</label>
                                    <select name="format" id="format" class="form-select">
                                        <option value="pdf">PDF</option>
                                        <option value="csv">CSV</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 date-range" style="display: none;">
                                    <label for="date_from" class="form-label">Date Range</label>
                                    <div class="input-group">
                                        <input type="date" name="date_from" id="date_from" class="form-control" required>
                                        <span class="input-group-text">to</span>
                                        <input type="date" name="date_to" id="date_to" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-download"></i> Generate Report
                                </button>
                            </div>
                        </form>

                        <!-- Preview Section -->
                        <div id="previewSection" class="preview-section">
                            <h6 class="mb-3">Report Preview</h6>
                            <div class="preview-table">
                                <table class="table table-striped">
                                    <thead>
                                        <tr id="previewHeaders"></tr>
                                    </thead>
                                    <tbody id="previewBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to select report type
        function selectReportType(type) {
            document.getElementById('report_type').value = type;
            
            // Update card styles
            document.querySelectorAll('.report-card').forEach(card => {
                card.classList.remove('border-primary');
            });
            event.currentTarget.classList.add('border-primary');
            
            // Show/hide date range based on report type
            const dateRange = document.querySelector('.date-range');
            dateRange.style.display = ['borrowings', 'feedback'].includes(type) ? 'block' : 'none';
            
            // Load preview data
            loadPreviewData(type);
        }

        // Function to load preview data
        async function loadPreviewData(type) {
            const previewSection = document.getElementById('previewSection');
            const previewHeaders = document.getElementById('previewHeaders');
            const previewBody = document.getElementById('previewBody');
            
            try {
                const response = await fetch(`get_preview_data.php?type=${type}`);
                const data = await response.json();
                
                if (data.length > 0) {
                    // Set headers
                    previewHeaders.innerHTML = Object.keys(data[0])
                        .map(key => `<th>${key.replace(/_/g, ' ').toUpperCase()}</th>`)
                        .join('');
                    
                    // Set preview data (limit to 5 rows)
                    previewBody.innerHTML = data.slice(0, 5)
                        .map(row => `
                            <tr>
                                ${Object.values(row)
                                    .map(value => `<td>${value}</td>`)
                                    .join('')}
                            </tr>
                        `).join('');
                    
                    previewSection.style.display = 'block';
                } else {
                    previewSection.style.display = 'none';
                }
            } catch (error) {
                console.error('Error loading preview data:', error);
                previewSection.style.display = 'none';
            }
        }

        // Set date range max to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('date_from').max = today;
        document.getElementById('date_to').max = today;
    </script>
</body>
</html>
